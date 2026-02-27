<script>
document.addEventListener("DOMContentLoaded", () => {

    // ------------------------------
    // LIVE SEARCH (AJAX)
    // ------------------------------
    const liveSearch = document.getElementById("liveSearch");
    const tableWrap = document.getElementById("employeeTable");
    if (liveSearch && tableWrap) {
        let timer = null;

        liveSearch.addEventListener("keyup", function () {
            const search = this.value || "";

            clearTimeout(timer);
            timer = setTimeout(() => {
                const params = new URLSearchParams({ search });
                fetch(`?${params.toString()}`, {
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                })
                .then(res => res.text())
                .then(html => { tableWrap.innerHTML = html; })
                .catch(() => {});
            }, 300);
        });
    }

    // ------------------------------
    // POSITION DROPDOWN (ADD MODAL)
    // ------------------------------
    const departments = @json($departments ?? []);
    const departmentSelect = document.getElementById("departmentSelect");
    const positionSelect = document.getElementById("positionSelect");

    if (departmentSelect && positionSelect) {
        departmentSelect.addEventListener("change", function () {
            const deptId = this.value;
            positionSelect.innerHTML = '<option value="">-- Select Position --</option>';

            const selected = departments.find(d => String(d.id) === String(deptId));
            if (selected && Array.isArray(selected.positions)) {
                selected.positions.forEach(pos => {
                    const opt = document.createElement("option");
                    opt.value = pos.id;
                    opt.textContent = pos.title;
                    positionSelect.appendChild(opt);
                });
            }
        });
    }

});
</script>