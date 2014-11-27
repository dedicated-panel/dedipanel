/**
 * This file is part of Dedipanel project
 *
 * (c) 2010-2014 Dedipanel <http://www.dedicated-panel.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    if ($('.progress').length > 0) {
        $('.progress').each(function (id, el) {
            var el = $(this);
            var val = parseInt(el.attr('value'));
            
            el.progressbar({
                value: val
            });
        });
    }
});