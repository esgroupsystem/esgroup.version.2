import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { AdjustmentEditor, type AdjustmentEditorProps } from '@/components/adjustments/adjustment-editor';
import { useModal } from '@/components/modal/modal-context';
import { Button } from '@/components/ui/button';
import { definePage } from '@/lib/define-page';

type Props = Pick<AdjustmentEditorProps, 'adjustment' | 'people' | 'types'> & {
    urls: { index: string; submit: string; offsetProof: string; overtimeCheck: string };
};

export default definePage<Props>({
    title: ({ adjustment }) => (adjustment ? 'Edit Payroll Attendance Adjustment' : 'New Payroll Attendance Adjustment'),
    description: () => 'Select a payroll-active biometric employee. The form saves employee_biometric_id and keeps old identifiers as snapshots.',
    actions: (props) => <BackLink {...props} />,
    // The editor has a side panel with the rule guide.
    size: 'xl',
    Content: ({ adjustment, people, types, urls }) => <AdjustmentEditor adjustment={adjustment} people={people} types={types} urls={urls} />,
});

function BackLink({ urls }: Props) {
    const modal = useModal();
    if (modal.inModal) return null;

    return (
        <Button variant="outline" asChild>
            <Link href={urls.index}>
                <ArrowLeft />
                Back
            </Link>
        </Button>
    );
}
