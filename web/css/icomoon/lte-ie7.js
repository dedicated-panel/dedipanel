/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'loops105\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-cabinet' : '&#x5f;',
			'icon-code' : '&#x5e;',
			'icon-analytics' : '&#x6f;',
			'icon-eye' : '&#x5d;',
			'icon-pictures' : '&#x5c;',
			'icon-speaker' : '&#x5b;',
			'icon-modem' : '&#x5a;',
			'icon-untitled' : '&#x59;',
			'icon-untitled-2' : '&#x58;',
			'icon-untitled-3' : '&#x57;',
			'icon-untitled-4' : '&#x56;',
			'icon-untitled-5' : '&#x55;',
			'icon-untitled-6' : '&#x54;',
			'icon-untitled-7' : '&#x53;',
			'icon-untitled-8' : '&#x52;',
			'icon-untitled-9' : '&#x51;',
			'icon-untitled-10' : '&#x50;',
			'icon-untitled-11' : '&#x4f;',
			'icon-untitled-12' : '&#x4e;',
			'icon-untitled-13' : '&#x4d;',
			'icon-untitled-14' : '&#x4c;',
			'icon-untitled-15' : '&#x4b;',
			'icon-untitled-16' : '&#x4a;',
			'icon-untitled-17' : '&#x49;',
			'icon-untitled-18' : '&#x48;',
			'icon-untitled-19' : '&#x47;',
			'icon-untitled-20' : '&#x46;',
			'icon-untitled-21' : '&#x45;',
			'icon-untitled-22' : '&#x44;',
			'icon-untitled-23' : '&#x43;',
			'icon-untitled-24' : '&#x42;',
			'icon-untitled-25' : '&#x41;',
			'icon-untitled-26' : '&#x40;',
			'icon-untitled-27' : '&#x3f;',
			'icon-untitled-28' : '&#x3e;',
			'icon-untitled-29' : '&#x3d;',
			'icon-untitled-30' : '&#x3c;',
			'icon-untitled-31' : '&#x3b;',
			'icon-untitled-32' : '&#x3a;',
			'icon-untitled-33' : '&#x39;',
			'icon-untitled-34' : '&#x38;',
			'icon-untitled-35' : '&#x37;',
			'icon-untitled-36' : '&#x36;',
			'icon-untitled-37' : '&#x35;',
			'icon-untitled-38' : '&#x34;',
			'icon-untitled-39' : '&#x33;',
			'icon-untitled-40' : '&#x32;',
			'icon-untitled-41' : '&#x31;',
			'icon-untitled-42' : '&#x30;',
			'icon-untitled-43' : '&#x2f;',
			'icon-untitled-44' : '&#x2e;',
			'icon-untitled-45' : '&#x2d;',
			'icon-untitled-46' : '&#x2c;',
			'icon-untitled-47' : '&#x2b;',
			'icon-untitled-48' : '&#x2a;',
			'icon-untitled-49' : '&#x29;',
			'icon-untitled-50' : '&#x28;',
			'icon-untitled-51' : '&#x27;',
			'icon-untitled-52' : '&#x26;',
			'icon-untitled-53' : '&#x25;',
			'icon-untitled-54' : '&#x24;',
			'icon-untitled-55' : '&#x23;',
			'icon-untitled-56' : '&#x22;',
			'icon-untitled-57' : '&#x21;',
			'icon-camera' : '&#x60;',
			'icon-eject' : '&#x61;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon');
		if (attr) {
			c = icons['icon-' + attr];
			if (c) {
				attr = c;
			}
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};