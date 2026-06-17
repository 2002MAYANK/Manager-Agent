document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('tableBody');
    if (!tableBody) return; // Not a datatable page

    const globalSearch = document.getElementById('globalSearch');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const paginationContainer = document.getElementById('paginationContainer');
    const totalCount = document.getElementById('totalCount');
    
    let state = {
        search: '',
        page: 1,
        sort_by: '',
        sort_dir: 'desc',
        status: '',
        date: ''
    };

    let searchTimeout;

    // Fetch data
    const fetchTableData = async () => {
        const url = new URL(window.location.href);
        // Clear old params, build from state
        url.search = '';
        if (state.search) url.searchParams.set('search', state.search);
        if (state.page > 1) url.searchParams.set('page', state.page);
        if (state.sort_by) url.searchParams.set('sort_by', state.sort_by);
        if (state.sort_dir) url.searchParams.set('sort_dir', state.sort_dir);
        if (state.status) url.searchParams.set('status', state.status);
        if (state.date) url.searchParams.set('date', state.date);

        try {
            tableBody.style.opacity = '0.5';
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                tableBody.innerHTML = data.tbody;
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                }
                if (totalCount) {
                    totalCount.textContent = `${data.total} total`;
                }
                updateSortIcons();
            }
        } catch (error) {
            console.error('Error fetching table data:', error);
        } finally {
            tableBody.style.transition = 'opacity 0.2s';
            tableBody.style.opacity = '1';
        }
    };

    // Update sort icons visually
    const updateSortIcons = () => {
        document.querySelectorAll('.sort-link').forEach(link => {
            const icon = link.querySelector('i');
            if (!icon) return;
            
            link.classList.remove('active', 'text-white');
            icon.className = 'bi bi-arrow-down-up ms-1 text-muted';
            
            if (link.dataset.sort === state.sort_by) {
                link.classList.add('active', 'text-white');
                icon.className = state.sort_dir === 'asc' 
                    ? 'bi bi-arrow-up text-white ms-1' 
                    : 'bi bi-arrow-down text-white ms-1';
            }
        });
    };

    // Listen to pagination clicks
    if (paginationContainer) {
        paginationContainer.addEventListener('click', (e) => {
            const link = e.target.closest('a.page-link');
            if (link) {
                e.preventDefault();
                const url = new URL(link.href);
                state.page = url.searchParams.get('page') || 1;
                fetchTableData();
            }
        });
    }

    // Listen to sort clicks
    document.addEventListener('click', (e) => {
        const sortLink = e.target.closest('.sort-link');
        if (sortLink) {
            e.preventDefault();
            const sortBy = sortLink.dataset.sort;
            
            if (state.sort_by === sortBy) {
                state.sort_dir = state.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort_by = sortBy;
                state.sort_dir = 'asc';
            }
            state.page = 1;
            fetchTableData();
        }
    });

    // Listen to global search
    if (globalSearch) {
        globalSearch.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                state.search = e.target.value;
                state.page = 1;
                fetchTableData();
            }, 300);
        });
    }

    // Listen to status filter
    if (statusFilter) {
        statusFilter.addEventListener('change', (e) => {
            state.status = e.target.value;
            state.page = 1;
            fetchTableData();
        });
    }

    // Listen to date filter
    if (dateFilter) {
        dateFilter.addEventListener('change', (e) => {
            state.date = e.target.value;
            state.page = 1;
            fetchTableData();
        });
    }
});
