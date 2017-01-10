/**
 * Copyright (c) 2016-2017 Invertus, JSC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

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