; (function ($) {
    $(document).ready(function () {
        let gpaRow = $('.sm_gpa_row');
        let isAddGPA = $('.form-table input#is_want_add_sm_gpa');

        function toggleGPARow()
        {
            gpaRow.toggle(isAddGPA.is(':checked'));
        }

        toggleGPARow();
        isAddGPA.on('change', toggleGPARow);

        //Student add subject mark group fields
        let fieldContainer = $('#group-fields-container');

        // Add new group field
        $('#add-group-field').on('click', function() {
            let newField = `
                <div class="group-field-row">
                    <input type="text" name="subject_name[]" placeholder="Subject Name">
                    <input type="number" name="mark[]" placeholder="Mark">
                    <input type="number" name="total_mark[]" placeholder="Total Mark">
                    <button type="button" class="remove-field">Remove</button>
                </div>`;
            fieldContainer.append(newField);
        });

        // Remove group field
        fieldContainer.on('click', '.remove-field', function() {
            $(this).parent('.group-field-row').remove();
        });

    });
})(jQuery);