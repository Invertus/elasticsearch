$(document).ready(function() {

    var $customRangesList = $('.brad-custom-ranges-list');
    var $addCustomRangeBtn = $('.brad-add-range-row');

    $addCustomRangeBtn.on('click', addEmptyCustomRangesRow);
    $customRangesList.sortable();

    /**
     * Add empty custom ranges row
     */
    function addEmptyCustomRangesRow()
    {
        var $nextId = getNextCustomRangesInputId();
        var $clone = $customRangesList.find('li:first').clone();

        $clone.find('input').each(function() {
            $(this).attr('name', $(this).attr('name').replace(/\d+/g, '') + $nextId);
            $(this).data('id', $nextId);
            $(this).val('');
        });

        $clone.insertAfter($customRangesList.find('li:last'));
        $customRangesList.sortable();
    }

    /**
     * Get next custom ranges input id
     *
     * @returns {number}
     */
    function getNextCustomRangesInputId()
    {
        var $max = 0;

        $customRangesList.find('li').each(function(){
            var $inputId = $(this).find('input:first').data('id');
            if ($inputId > $max) {
                $max = $inputId;
            }
        });

        return $max + 1;
    }
});