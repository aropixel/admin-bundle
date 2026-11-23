import { onDomReady } from '/bundles/aropixeladmin/js/utils/dom-ready.js';

// Both the Ajax and the basic tables get the search placeholder; 2.x set it on every table.
const setFilterPlaceholder = (table) => {
    const input = table.closest('.dataTables_wrapper')?.querySelector('.dataTables_filter input');
    if (input) {
        input.setAttribute('placeholder', $.fn.dataTable.defaults.language?.filterPlaceholder || 'Filtrer...');
    }

    return input;
};

onDomReady(() => {
    document.querySelectorAll('.datatable-ajax').forEach((table) => {
        const url = table.dataset.src || table.dataset.ajaxUrl || window.location.pathname;
        if (typeof $.fn.dataTable.pipeline !== 'function') {
            console.warn('DataTables pipeline plugin is not loaded');
            return;
        }

        const pages = 5;
        const params = {
            processing: true,
            serverSide: true,
            order: [],
            ajax: $.fn.dataTable.pipeline({ url, pages })
        };

        if (table.dataset.orderColumn !== undefined && table.dataset.orderColumn !== '') {
            const column = parseInt(table.dataset.orderColumn);
            const direction = table.dataset.orderDirection || 'asc';
            params.order = [[column, direction]];
        }

        $(table).dataTable(params);
        setFilterPlaceholder(table);
    });


    // Basic datatables
    document.querySelectorAll('.datatable').forEach((el) => {
        const table = $(el);
        const params = {};

        const col = table.data('order-column');
        const dir = table.data('order-direction') || 'asc';
        if (col !== undefined && col !== null) params.order = [[col, dir]];

        const dt = table.dataTable(params);

        const input = setFilterPlaceholder(el);
        if (input) {
            input.addEventListener('keyup', function () {
                const regEx = '\b' + this.value;
                dt.search(regEx, true, false).draw();
            });
        }
    });
});
