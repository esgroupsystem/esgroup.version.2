import { ContributionCalculator } from '@/components/payroll/settings/contribution-calculator';
import { SettingsTabs } from '@/components/payroll/settings/settings-tabs';
import { TestPanel } from '@/components/payroll/settings/test-panel';
import type { SettingsTabUrls, TestOptions } from '@/components/payroll/settings/types';
import { definePage } from '@/lib/define-page';

interface Props {
    test: TestOptions;
    current: { label: string; effective_from: string | null };
    urls: SettingsTabUrls;
}

export default definePage<Props>({
    title: () => 'Payroll Settings',
    description: ({ current }) =>
        `Try the payroll computation on real attendance without saving anything. Each cutoff uses the settings version of its own dates (today: ${current.label}).`,
    size: 'xl',
    Content: TestPage,
});

function TestPage({ test, urls }: Props) {
    return (
        <div className="grid min-w-0 gap-4">
            <SettingsTabs active="test" urls={urls} />
            <TestPanel options={test} allowVersionCompare />
            <ContributionCalculator url={test.urls.contributions} />
        </div>
    );
}
