import { PrintShell, printTable } from '@/components/print/print-shell';

interface Props {
    rows: { id: number; bus: string; reporter: string | null; issue: string; details: string | null; status: string; date: string | null }[];
    status: string;
    search: string;
    generated: string;
}

export default function CctvPrint({ rows, status, search, generated }: Props) {
    return (
        <PrintShell
            title="CCTV Job Orders Report"
            subtitle={`Status: ${status} · Search: ${search || 'None'}`}
            meta={
                <>
                    Generated {generated}
                    <br />
                    {rows.length.toLocaleString()} record{rows.length === 1 ? '' : 's'}
                </>
            }
            landscape
        >
            <table className={printTable}>
                <thead>
                    <tr>
                        <th>Bus</th>
                        <th>Reporter</th>
                        <th>Issue</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    {rows.length === 0 && (
                        <tr>
                            <td colSpan={6} className="text-center">
                                No job orders found.
                            </td>
                        </tr>
                    )}
                    {rows.map((row) => (
                        <tr key={row.id}>
                            <td>{row.bus}</td>
                            <td>{row.reporter || '—'}</td>
                            <td>{row.issue}</td>
                            <td className="whitespace-pre-line">{row.details || '—'}</td>
                            <td className="font-bold">{row.status}</td>
                            <td className="whitespace-nowrap">{row.date}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </PrintShell>
    );
}
