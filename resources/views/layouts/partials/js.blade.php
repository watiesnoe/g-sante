    <script src="{{ asset('admin/js/dashmix.app.min.js') }}"></script>

    <!-- jQuery (requis pour DataTables) -->
    <script src="{{ asset('admin/js/lib/jquery.min.js') }}"></script>

    <!-- DataTables core + Bootstrap 5 -->
    <script src="{{ asset('admin/js/plugins/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>

    {{--
        ⚡ PERFORMANCE : pdfmake + vfs_fonts (~2MB) et buttons sont retirés du layout global.
        Chargez-les uniquement dans les pages qui exportent des PDF/Excel via :
        @push('scripts_heavy')
            <script src="{{ asset('admin/js/plugins/datatables-buttons/dataTables.buttons.min.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons-jszip/jszip.min.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons-pdfmake/pdfmake.min.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons-pdfmake/vfs_fonts.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons/buttons.print.min.js') }}"></script>
            <script src="{{ asset('admin/js/plugins/datatables-buttons/buttons.html5.min.js') }}"></script>
        @endpush
    --}}
    @stack('scripts_heavy')

    <!-- Select2 + validation -->
    <script src="{{ asset('admin/js/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/jquery-validation/additional-methods.js') }}"></script>
    <script src="{{ asset('admin/js/plugins/bootstrap-notify/bootstrap-notify.min.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>

    <!-- Select2 init global -->
    <script>
        $(document).ready(function () {
            $('.js-select2').select2({
                placeholder: "-- Sélectionner un patient existant --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <!-- Price/Currency Inputs Dynamic Formatting -->
    <script>
        // Helper to format values as currency/prices
        function formatPriceValue(val) {
            if (val === undefined || val === null) return '';
            let str = val.toString();
            
            // Remove all non-numeric characters except comma and dot
            let clean = str.replace(/[^0-9,.]/g, '');
            
            // If it has comma, convert to dot for standard decimal processing
            clean = clean.replace(/,/g, '.');
            
            // Handle multiple decimal points (keep only the first one)
            let parts = clean.split('.');
            if (parts.length > 2) {
                clean = parts[0] + '.' + parts.slice(1).join('');
            }
            
            let integerPart = parts[0];
            let decimalPart = parts[1];
            
            // Format integer part with spaces
            let formattedInt = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            
            if (decimalPart !== undefined) {
                // Limit decimal part to 2 digits
                return formattedInt + ',' + decimalPart.substring(0, 2);
            }
            return formattedInt;
        }

        // Helper to get raw numeric value for database/backend
        function getRawNumericValue(val) {
            if (val === undefined || val === null) return '';
            let str = val.toString();
            
            // Remove everything except numbers, commas and dots
            let clean = str.replace(/[^0-9,.]/g, '');
            
            // Replace comma with dot
            clean = clean.replace(/,/g, '.');
            
            // Handle multiple decimal points
            let parts = clean.split('.');
            if (parts.length > 2) {
                clean = parts[0] + '.' + parts.slice(1).join('');
            }
            
            return clean;
        }

        function initPriceInputs() {
            $('.price-input').each(function() {
                let $input = $(this);
                if ($input.data('price-initialized')) return;
                
                $input.data('price-initialized', true);
                
                // Save raw value or ensure correct initial format
                let initialVal = $input.val();
                
                // If type is number, change it to text
                if ($input.attr('type') === 'number') {
                    $input.attr('type', 'text');
                }
                
                if (initialVal !== undefined && initialVal !== '') {
                    $input.val(formatPriceValue(initialVal));
                }
            });
        }

        // Expose helpers globally
        window.formatPriceValue = formatPriceValue;
        window.getRawNumericValue = getRawNumericValue;
        window.initPriceInputs = initPriceInputs;

        $(document).ready(function() {
            initPriceInputs();

            // Handle typing / formatting dynamically
            $(document).on('input', '.price-input', function(e) {
                let input = this;
                let originalValue = input.value;
                let cursorPosition = input.selectionStart;
                
                let formatted = formatPriceValue(originalValue);
                
                if (originalValue !== formatted) {
                    input.value = formatted;
                    
                    // Adjust cursor position to avoid jumping
                    let lengthDifference = formatted.length - originalValue.length;
                    let newCursorPos = cursorPosition + lengthDifference;
                    newCursorPos = Math.max(0, Math.min(newCursorPos, formatted.length));
                    input.setSelectionRange(newCursorPos, newCursorPos);
                }
            });

            // Initialize on dynamically added elements / modals / AJAX completions
            $(document).on('shown.bs.modal', function() {
                initPriceInputs();
                // Format existing populated values in modal
                $('.price-input').each(function() {
                    let val = $(this).val();
                    if (val) {
                        $(this).val(formatPriceValue(val));
                    }
                });
            });

            // Handle focusin double-check for dynamic elements (e.g. dynamic tables)
            $(document).on('focusin', '.price-input', function() {
                let $input = $(this);
                if (!$input.data('price-initialized')) {
                    $input.data('price-initialized', true);
                    if ($input.attr('type') === 'number') {
                        $input.attr('type', 'text');
                    }
                    let val = $input.val();
                    if (val) {
                        $input.val(formatPriceValue(val));
                    }
                }
            });

            // Intercept submit events on window in capturing phase to clean values before submission
            window.addEventListener('submit', function(e) {
                let form = e.target;
                if (form && form.tagName === 'FORM') {
                    let $form = $(form);
                    let formattedInputs = $form.find('.price-input');
                    let originalValues = [];
                    
                    formattedInputs.each(function() {
                        originalValues.push({
                            el: this,
                            val: this.value
                        });
                        this.value = getRawNumericValue(this.value);
                    });
                    
                    // Restore formatted values after submit begins or in next tick
                    setTimeout(function() {
                        originalValues.forEach(function(item) {
                            item.el.value = formatPriceValue(item.val);
                        });
                    }, 50);
                }
            }, true);

            // Backup and hook jQuery's serialize method
            if ($.fn.serialize) {
                const originalSerialize = $.fn.serialize;
                $.fn.serialize = function() {
                    let $form = this;
                    let formattedInputs = $form.find('.price-input');
                    let originalValues = [];
                    
                    formattedInputs.each(function() {
                        originalValues.push({
                            el: this,
                            val: this.value
                        });
                        this.value = getRawNumericValue(this.value);
                    });
                    
                    let result = originalSerialize.apply(this, arguments);
                    
                    originalValues.forEach(function(item) {
                        item.el.value = item.val;
                    });
                    
                    return result;
                };
            }

            // Backup and hook jQuery's serializeArray method
            if ($.fn.serializeArray) {
                const originalSerializeArray = $.fn.serializeArray;
                $.fn.serializeArray = function() {
                    let $form = this;
                    let formattedInputs = $form.find('.price-input');
                    let originalValues = [];
                    
                    formattedInputs.each(function() {
                        originalValues.push({
                            el: this,
                            val: this.value
                        });
                        this.value = getRawNumericValue(this.value);
                    });
                    
                    let result = originalSerializeArray.apply(this, arguments);
                    
                    originalValues.forEach(function(item) {
                        item.el.value = item.val;
                    });
                    
                    return result;
                };
            }
        });
    </script>

