<?php

declare(strict_types=1);

namespace App\Support\Payroll;

use InvalidArgumentException;

/**
 * Safe calculator for Payroll Rule formulas, e.g. `IF(days_absent = 0 AND minutes_late = 0, 1000, 0)`.
 *
 * It only does math: numbers, named values, + - * / ^, a postfix % (5% = 0.05), comparisons,
 * AND / OR / NOT and the functions IF, MIN, MAX, ROUND, FLOOR, CEIL, ABS. It never runs code.
 * Names are case-insensitive, a single "=" means "equals", IF only evaluates the branch it picks,
 * and a division by zero gives 0 so one employee's data can never break a payroll run.
 */
final class PayrollFormula
{
    public const MAX_LENGTH = 1000;

    /** function => [min args, max args] */
    private const FUNCTIONS = [
        'if' => [3, 3],
        'min' => [1, 50],
        'max' => [1, 50],
        'round' => [1, 2],
        'floor' => [1, 1],
        'ceil' => [1, 1],
        'abs' => [1, 1],
    ];

    /** binary operator => [left binding power, right binding power] */
    private const BINARY = [
        'or' => [1, 2],
        'and' => [3, 4],
        '==' => [5, 6],
        '!=' => [5, 6],
        '<' => [7, 8],
        '<=' => [7, 8],
        '>' => [7, 8],
        '>=' => [7, 8],
        '+' => [9, 10],
        '-' => [9, 10],
        '*' => [11, 12],
        '/' => [11, 12],
        '^' => [16, 15],
    ];

    private const PREFIX_POWER = 13;

    private const POSTFIX_POWER = 17;

    /** @var list<array{type: string, value: string, pos: int}> */
    private array $tokens = [];

    private int $index = 0;

    /**
     * Parse a formula and check every name against the allowed list.
     *
     * @param  list<string>  $names  allowed value names (lower case)
     * @return array<string, mixed> the parsed tree
     *
     * @throws InvalidArgumentException with a message fit to show the user
     */
    public static function parse(string $formula, array $names): array
    {
        $parser = new self;
        $tree = $parser->parseFormula($formula);
        $unknown = array_values(array_diff(self::namesIn($tree), $names));

        if ($unknown !== []) {
            throw new InvalidArgumentException('Unknown value: '.implode(', ', $unknown).'. Pick a name from the list of values.');
        }

        return $tree;
    }

    /**
     * @param  array<string, float|int|bool>  $values
     *
     * @throws InvalidArgumentException
     */
    public static function evaluate(string $formula, array $values): float
    {
        $values = array_change_key_case($values, CASE_LOWER);
        $tree = self::parse($formula, array_keys($values));
        $result = self::run($tree, $values);

        return is_finite($result) ? $result : 0.0;
    }

    /**
     * Value names a formula uses (lower case, unique).
     *
     * @return list<string>
     */
    public static function referencedNames(string $formula): array
    {
        return self::namesIn((new self)->parseFormula($formula));
    }

    /** @return array<string, mixed> */
    private function parseFormula(string $formula): array
    {
        $formula = trim($formula);

        if ($formula === '') {
            throw new InvalidArgumentException('The formula is empty.');
        }

        if (mb_strlen($formula) > self::MAX_LENGTH) {
            throw new InvalidArgumentException('The formula is too long (max '.self::MAX_LENGTH.' characters).');
        }

        $this->tokens = $this->tokenize($formula);
        $this->index = 0;
        $tree = $this->expression(0, 0);

        if ($this->peek()['type'] !== 'end') {
            $token = $this->peek();

            throw new InvalidArgumentException(sprintf('Unexpected "%s" at character %d.', $token['value'], $token['pos'] + 1));
        }

        return $tree;
    }

    /** @return list<array{type: string, value: string, pos: int}> */
    private function tokenize(string $formula): array
    {
        $tokens = [];
        $length = strlen($formula);
        $pos = 0;

        while ($pos < $length) {
            $char = $formula[$pos];

            if (ctype_space($char)) {
                $pos++;

                continue;
            }

            if (preg_match('/\G(\d+(\.\d+)?|\.\d+)/', $formula, $match, 0, $pos)) {
                $tokens[] = ['type' => 'number', 'value' => $match[0], 'pos' => $pos];
                $pos += strlen($match[0]);

                continue;
            }

            if (preg_match('/\G[A-Za-z_][A-Za-z0-9_]*/', $formula, $match, 0, $pos)) {
                $word = strtolower($match[0]);
                $type = in_array($word, ['and', 'or', 'not', 'true', 'false'], true) ? $word : 'name';
                $tokens[] = ['type' => $type, 'value' => $word, 'pos' => $pos];
                $pos += strlen($match[0]);

                continue;
            }

            $two = substr($formula, $pos, 2);
            $operator = match ($two) {
                '<=', '>=', '!=', '==' => $two,
                '<>' => '!=',
                '&&' => 'and',
                '||' => 'or',
                default => null,
            };

            if ($operator !== null) {
                $tokens[] = ['type' => in_array($operator, ['and', 'or'], true) ? $operator : 'op', 'value' => $operator, 'pos' => $pos];
                $pos += 2;

                continue;
            }

            $operator = match ($char) {
                '+', '-', '*', '/', '^', '<', '>', '%', '(', ')', ',' => $char,
                '=' => '==',
                '!' => 'not',
                default => null,
            };

            if ($operator === null) {
                throw new InvalidArgumentException(sprintf('"%s" is not allowed (character %d).', $char, $pos + 1));
            }

            $type = match ($operator) {
                '(', ')', ',' => $operator,
                'not' => 'not',
                default => 'op',
            };
            $tokens[] = ['type' => $type, 'value' => $operator, 'pos' => $pos];
            $pos++;
        }

        $tokens[] = ['type' => 'end', 'value' => 'end of formula', 'pos' => $length];

        return $tokens;
    }

    /** @return array<string, mixed> */
    private function expression(int $minPower, int $depth): array
    {
        if ($depth > 60) {
            throw new InvalidArgumentException('The formula is nested too deeply.');
        }

        $token = $this->next();

        $left = match (true) {
            $token['type'] === 'number' => ['n' => (float) $token['value']],
            $token['type'] === 'true' => ['n' => 1.0],
            $token['type'] === 'false' => ['n' => 0.0],
            $token['type'] === 'name' => $this->nameOrCall($token, $depth),
            $token['type'] === '(' => $this->group($depth),
            $token['type'] === 'not' => ['u' => 'not', 'a' => $this->expression(self::PREFIX_POWER, $depth + 1)],
            $token['type'] === 'op' && in_array($token['value'], ['-', '+'], true) => ['u' => $token['value'], 'a' => $this->expression(self::PREFIX_POWER, $depth + 1)],
            default => throw new InvalidArgumentException(
                $token['type'] === 'end'
                    ? 'The formula ends too early.'
                    : sprintf('Unexpected "%s" at character %d.', $token['value'], $token['pos'] + 1)
            ),
        };

        while (true) {
            $token = $this->peek();

            if ($token['type'] === 'op' && $token['value'] === '%') {
                if ($minPower > self::POSTFIX_POWER) {
                    break;
                }

                $this->next();
                $left = ['b' => '/', 'l' => $left, 'r' => ['n' => 100.0]];

                continue;
            }

            $operator = in_array($token['type'], ['op', 'and', 'or'], true) ? $token['value'] : null;

            if ($operator === null || ! isset(self::BINARY[$operator])) {
                break;
            }

            [$leftPower, $rightPower] = self::BINARY[$operator];

            if ($leftPower < $minPower) {
                break;
            }

            $this->next();
            $left = ['b' => $operator, 'l' => $left, 'r' => $this->expression($rightPower, $depth + 1)];
        }

        return $left;
    }

    /**
     * @param  array{type: string, value: string, pos: int}  $token
     * @return array<string, mixed>
     */
    private function nameOrCall(array $token, int $depth): array
    {
        if ($this->peek()['type'] !== '(') {
            return ['v' => $token['value']];
        }

        $function = $token['value'];

        if (! isset(self::FUNCTIONS[$function])) {
            throw new InvalidArgumentException(sprintf('Unknown function "%s". Use IF, MIN, MAX, ROUND, FLOOR, CEIL or ABS.', strtoupper($function)));
        }

        $this->next();
        $args = [];

        if ($this->peek()['type'] !== ')') {
            do {
                $args[] = $this->expression(0, $depth + 1);
            } while ($this->accept(','));
        }

        $this->expect(')');
        [$min, $max] = self::FUNCTIONS[$function];

        if (count($args) < $min || count($args) > $max) {
            $expected = $min === $max ? (string) $min : $min.' to '.$max;

            throw new InvalidArgumentException(sprintf('%s needs %s value(s), got %d.', strtoupper($function), $expected, count($args)));
        }

        return ['f' => $function, 'args' => $args];
    }

    /** @return array<string, mixed> */
    private function group(int $depth): array
    {
        $inner = $this->expression(0, $depth + 1);
        $this->expect(')');

        return $inner;
    }

    /** @return array{type: string, value: string, pos: int} */
    private function peek(): array
    {
        return $this->tokens[$this->index];
    }

    /** @return array{type: string, value: string, pos: int} */
    private function next(): array
    {
        $token = $this->tokens[$this->index];

        if ($token['type'] !== 'end') {
            $this->index++;
        }

        return $token;
    }

    private function accept(string $type): bool
    {
        if ($this->peek()['type'] === $type) {
            $this->next();

            return true;
        }

        return false;
    }

    private function expect(string $type): void
    {
        if (! $this->accept($type)) {
            $token = $this->peek();

            throw new InvalidArgumentException($token['type'] === 'end'
                ? sprintf('Missing "%s" at the end of the formula.', $type)
                : sprintf('Expected "%s" at character %d.', $type, $token['pos'] + 1));
        }
    }

    /**
     * @param  array<string, mixed>  $tree
     * @return list<string>
     */
    private static function namesIn(array $tree): array
    {
        $names = [];
        $walk = function (array $node) use (&$walk, &$names): void {
            if (isset($node['v'])) {
                $names[$node['v']] = true;
            }

            foreach (['a', 'l', 'r'] as $key) {
                if (isset($node[$key])) {
                    $walk($node[$key]);
                }
            }

            foreach ($node['args'] ?? [] as $arg) {
                $walk($arg);
            }
        };
        $walk($tree);

        return array_keys($names);
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array<string, float|int|bool>  $values
     */
    private static function run(array $node, array $values): float
    {
        if (isset($node['n'])) {
            return (float) $node['n'];
        }

        if (isset($node['v'])) {
            return (float) ($values[$node['v']] ?? 0);
        }

        if (isset($node['u'])) {
            $value = self::run($node['a'], $values);

            return match ($node['u']) {
                '-' => -$value,
                'not' => $value == 0.0 ? 1.0 : 0.0,
                default => $value,
            };
        }

        if (isset($node['f'])) {
            return self::call($node['f'], $node['args'], $values);
        }

        $operator = $node['b'];

        if ($operator === 'and') {
            return self::run($node['l'], $values) != 0.0 && self::run($node['r'], $values) != 0.0 ? 1.0 : 0.0;
        }

        if ($operator === 'or') {
            return self::run($node['l'], $values) != 0.0 || self::run($node['r'], $values) != 0.0 ? 1.0 : 0.0;
        }

        $left = self::run($node['l'], $values);
        $right = self::run($node['r'], $values);

        return match ($operator) {
            '+' => $left + $right,
            '-' => $left - $right,
            '*' => $left * $right,
            '/' => $right == 0.0 ? 0.0 : $left / $right,
            '^' => self::power($left, $right),
            '==' => abs($left - $right) < 0.000001 ? 1.0 : 0.0,
            '!=' => abs($left - $right) >= 0.000001 ? 1.0 : 0.0,
            '<' => $left < $right ? 1.0 : 0.0,
            '<=' => $left <= $right ? 1.0 : 0.0,
            '>' => $left > $right ? 1.0 : 0.0,
            '>=' => $left >= $right ? 1.0 : 0.0,
            default => 0.0,
        };
    }

    /**
     * @param  list<array<string, mixed>>  $args
     * @param  array<string, float|int|bool>  $values
     */
    private static function call(string $function, array $args, array $values): float
    {
        if ($function === 'if') {
            return self::run($args[0], $values) != 0.0
                ? self::run($args[1], $values)
                : self::run($args[2], $values);
        }

        $numbers = array_map(fn (array $arg): float => self::run($arg, $values), $args);

        return match ($function) {
            'min' => min($numbers),
            'max' => max($numbers),
            'round' => round($numbers[0], max(0, min(6, (int) ($numbers[1] ?? 0)))),
            'floor' => floor($numbers[0]),
            'ceil' => ceil($numbers[0]),
            'abs' => abs($numbers[0]),
            default => 0.0,
        };
    }

    private static function power(float $base, float $exponent): float
    {
        if ($base < 0 && floor($exponent) != $exponent) {
            return 0.0;
        }

        $result = $base ** $exponent;

        return is_finite((float) $result) ? (float) $result : 0.0;
    }
}
