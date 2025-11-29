<x-layouts.app title="منصة باشنور - الرصيد">

    <x-balance.summary-card :totalBalance="$totalBalance" />

    <x-balance.transactions-container />

    <x-balance.transaction-modal />

<x-slot:scripts>
    <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;

            // Load transactions on page load
            loadTransactions(currentPage);

            // Load transactions function
            function loadTransactions(page = 1) {
                $('#loadingState').removeClass('hidden');
                $('#emptyState').addClass('hidden');
                $('#transactionsTable').addClass('hidden');
                $('#paginationContainer').addClass('hidden');

                $.ajax({
                    url: '/api/balances?page=' + page,
                    method: 'GET',
                    success: function(response) {
                        $('#loadingState').addClass('hidden');

                            if (response.success && response.data.data && response.data.data.length > 0) {
                                renderTransactions(response.data.data);
                                renderPagination(response.data);
                                $('#transactionsTable').removeClass('hidden');
                                $('#paginationContainer').removeClass('hidden');
                            } else {
                                $('#emptyState').removeClass('hidden');
                            }
                        },
                        error: function(xhr) {
                            $('#loadingState').addClass('hidden');
                            $('#emptyState').removeClass('hidden');
                            console.error('Error loading transactions:', xhr);
                            alert('حدث خطأ أثناء تحميل العمليات');
                        }
                    });
                }

            // Render transactions
            function renderTransactions(transactions) {
                const tbody = $('#transactionsBody');
                tbody.empty();

                transactions.forEach(transaction => {
                    const date = new Date(transaction.processCreated);
                    const day = date.getDate();
                    const month = date.toLocaleDateString('ar-SA', {
                        month: 'long'
                    });

                    const processTypeText = transaction.processType === 'income' ? 'إيداع' : 'سحب';
                    const processTypeClass = transaction.processType === 'income' ?
                        'text-green-700 bg-green-100' : 'text-blue-700 bg-blue-100';

                    const statusText = transaction.processStatus === 'complete' ? 'مكتمل' : 'قيد التنفيذ';
                    const statusClass = transaction.processStatus === 'complete' ?
                        'text-green-700 bg-green-100' : 'text-yellow-700 bg-yellow-100';

                        const row = `
                            <tr class="border-b cursor-pointer bg-neutral-primary border-default hover:bg-gray-50 transaction-row" data-id="${transaction.id}">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2 justify-center items-center">
                                        <h4 class="font-semibold text-gray-800">${day}</h4>
                                        <span class="inline-block text-xs text-gray-500 w-fit">${month}</span>
                                    </div>
                                </td>
                                <td class="flex px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <h4 class="font-semibold text-gray-800">عملية ${processTypeText}</h4>
                                        <div class="flex gap-2">
                                            <span class="inline-block px-3 py-1 text-sm rounded-full w-fit ${processTypeClass}">
                                                ${processTypeText}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <span class="inline-block px-3 py-1 text-sm rounded-full w-fit ${statusClass}">
                                            ${statusText}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2 justify-center items-center">
                                        <h4 class="font-semibold text-gray-800">${transaction.totalAmount} ر.س</h4>
                                        <span class="inline-block text-xs text-gray-500 w-fit">المبلغ الإجمالي</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="bg-gradient-to-r from-sky-400 to-indigo-900 p-[1px] rounded-lg">
                                        <button class="px-4 py-3 w-full font-semibold text-indigo-900 bg-white rounded-lg view-details-btn">
                                            التفاصيل
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;

                    tbody.append(row);
                });

                // Add click event for transaction rows
                $('.transaction-row').on('click', function() {
                    const balanceId = $(this).data('id');
                    openTransactionModal(balanceId);
                });
            }

            // Render pagination
            function renderPagination(data) {
                const container = $('#paginationContainer');
                container.empty();

                if (data.last_page <= 1) {
                    container.addClass('hidden');
                    return;
                }

                container.removeClass('hidden');

                // Previous button
                if (data.current_page > 1) {
                    container.append(`
                <button class="px-4 py-2 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 pagination-btn" data-page="${data.current_page - 1}">
                    السابق
                </button>`);
                }

                // Page numbers
                for (let i = 1; i <= data.last_page; i++) {
                    const activeClass = i === data.current_page ? 'bg-indigo-900 text-white' :
                        'bg-white border border-gray-300 hover:bg-gray-50';
                    container.append(`
                <button class="px-4 py-2 rounded-lg pagination-btn ${activeClass}" data-page="${i}">
                    ${i}
                </button>`);
                }

                // Next button
                if (data.current_page < data.last_page) {
                    container.append(`
                <button class="px-4 py-2 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 pagination-btn" data-page="${data.current_page + 1}">
                    التالي
                </button>`);
                }

                // Add click event for pagination buttons
                $('.pagination-btn').on('click', function() {
                    const page = $(this).data('page');
                    currentPage = page;
                    loadTransactions(page);
                });
            }

            // Open transaction modal
            function openTransactionModal(balanceId) {
                $('#transactionModal').removeClass('hidden');
                $('#transactionDetails').addClass('hidden');
                $('#modalLoading').removeClass('hidden');

                setTimeout(function() {
                    $('#modalContent').removeClass('-translate-x-full');
                }, 10);

                // Load transaction details
                $.ajax({
                    url: '/api/balances/' + balanceId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            renderTransactionDetails(response.data);
                            $('#modalLoading').addClass('hidden');
                            $('#transactionDetails').removeClass('hidden');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading transaction details:', xhr);
                        alert('حدث خطأ أثناء تحميل تفاصيل العملية');
                        closeModal();
                    }
                });
            }

            // Render transaction details
            function renderTransactionDetails(data) {
                // Process Type Badge
                const processTypeText = data.processType === 'income' ? 'إيداع' : 'سحب';
                const processTypeClass = data.processType === 'income' ? 'bg-green-100 text-green-700' :
                    'bg-blue-100 text-blue-700';
                $('#processTypeBadge').text(processTypeText).removeClass().addClass(
                    `inline-block px-4 py-2 rounded-full text-sm font-semibold ${processTypeClass}`);

                // Amounts
                $('#baseAmount').text((data.processAmount || 0) + ' ر.س');
                $('#commissionAmount').text((data.commissionAmount || 0) + ' ر.س');
                $('#vatAmount').text((data.vatAmount || 0) + ' ر.س');

                // Discount (show only if exists)
                if (data.discountAmount && data.discountAmount > 0) {
                    $('#discountAmount').text((data.discountAmount || 0) + ' ر.س');
                    $('#discountSection').removeClass('hidden');
                } else {
                    $('#discountSection').addClass('hidden');
                }

                $('#totalAmount').text((data.totalAmount || 0) + ' ر.س');

                // Transaction Info
                $('#transactionRef').text(data.transactionRef || 'غير متوفر');
                $('#memberName').text(data.memberName || 'غير متوفر');
                
                // Member Type
                const memberTypeText = data.memberType === 'client' ? 'عميل' : (data.memberType ==='freelancer' ? 'فريلانسر' : data.memberType || 'غير متوفر');
                $('#memberType').text(memberTypeText);

                // Project (show only if exists)
                if (data.projectId) {
                    $('#projectId').text('#' + data.projectId);
                    $('#projectSection').removeClass('hidden');
                } else {
                    $('#projectSection').addClass('hidden');
                }
                
                // Project Title (show only if exists)
                if (data.projectTitle) {
                    $('#projectTitle').text(data.projectTitle);
                    $('#projectTitleSection').removeClass('hidden');
                } else {
                    $('#projectTitleSection').addClass('hidden');
                }

                // Status
                const statusText = data.processStatus === 'complete' ? 'مكتمل' : 'قيد التنفيذ';
                const statusClass = data.processStatus === 'complete' ? 'bg-green-100 text-green-700' :
                    'bg-yellow-100 text-yellow-700';
                $('#statusBadge').text(statusText).removeClass().addClass(
                    `px-3 py-1 rounded-full text-sm font-semibold ${statusClass}`);

                // Created At
                const date = new Date(data.processCreated);
                $('#createdAt').text(date.toLocaleDateString('ar-SA') + ' ' + date.toLocaleTimeString('ar-SA'));

                // Additional Data
                if (data.additionalData && Object.keys(data.additionalData).length > 0) {
                    let additionalHtml = '';
                    
                    // Translate keys to Arabic
                    const translations = {
                        'payment_method': 'طريقة الدفع',
                        'card_last4': 'آخر 4 أرقام من البطاقة',
                        'payment_status': 'حالة الدفع',
                        'payment_date': 'تاريخ الدفع',
                        'bank_account': 'رقم الحساب البنكي',
                        'bank_name': 'اسم البنك',
                        'payout_status': 'حالة السحب',
                        'payout_date': 'تاريخ السحب',
                        'credit_card': 'بطاقة ائتمان',
                        'debit_card': 'بطاقة مدين',
                        'bank_transfer': 'تحويل بنكي',
                        'completed': 'مكتمل',
                        'pending': 'قيد الانتظار',
                        'processing': 'قيد المعالجة'
                    };
                    
                    for (const [key, value] of Object.entries(data.additionalData)) {
                        const translatedKey = translations[key] || key;
                        let translatedValue = value;
                        
                        // Translate common values
                        if (translations[value]) {
                            translatedValue = translations[value];
                        }
                        
                        // Format dates if value looks like a date
                        if (typeof value === 'string' && value.match(/\d{4}-\d{2}-\d{2}/)) {
                            const dateObj = new Date(value);
                            translatedValue = dateObj.toLocaleDateString('ar-SA') + ' ' + dateObj
                                .toLocaleTimeString('ar-SA', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                        }
                        additionalHtml += `
                            <div class="flex justify-between items-center py-2 border-b border-sky-200 last:border-0">
                                <span class="text-sm text-gray-600">${translatedKey}</span>
                                <span class="text-sm font-semibold text-gray-800">${translatedValue}</span>
                            </div>
                        `;
                    }
                    $('#additionalData').html(additionalHtml);
                    $('#additionalDataSection').removeClass('hidden');
                } else {
                    $('#additionalDataSection').addClass('hidden');
                }
            }

            // Close modal
            function closeModal() {
                $('#modalContent').addClass('-translate-x-full');
                setTimeout(function() {
                    $('#transactionModal').addClass('hidden');
                }, 300);
            }

            $('#closeModal').on('click', function() {
                closeModal();
            });

            $('#transactionModal').on('click', function(e) {
                if (e.target.id === 'transactionModal') {
                    closeModal();
                }
            });

            // Withdraw button (placeholder)
            $('#withdrawBtn').on('click', function() {
                alert('وظيفة السحب ستكون متاحة قريباً');
            });
        });
    </script>
</x-slot:scripts>

</x-layouts.app>
