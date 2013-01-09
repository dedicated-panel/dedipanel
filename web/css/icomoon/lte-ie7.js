/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'iconuntitled' : '&#x21;',
			'iconuntitled-2' : '&#x22;',
			'iconuntitled-3' : '&#x23;',
			'iconuntitled-4' : '&#x24;',
			'iconuntitled-5' : '&#x25;',
			'iconuntitled-6' : '&#x26;',
			'iconuntitled-7' : '&#x27;',
			'iconuntitled-8' : '&#x28;',
			'iconuntitled-9' : '&#x29;',
			'iconuntitled-10' : '&#x2a;',
			'iconuntitled-11' : '&#x2b;',
			'iconuntitled-12' : '&#x2c;',
			'iconuntitled-13' : '&#x2d;',
			'iconuntitled-14' : '&#x2e;',
			'iconuntitled-15' : '&#x2f;',
			'iconuntitled-16' : '&#x30;',
			'iconuntitled-17' : '&#x31;',
			'iconuntitled-18' : '&#x32;',
			'iconuntitled-19' : '&#x33;',
			'iconuntitled-20' : '&#x34;',
			'iconuntitled-21' : '&#x35;',
			'iconuntitled-22' : '&#x36;',
			'iconuntitled-23' : '&#x37;',
			'iconuntitled-24' : '&#x38;',
			'iconuntitled-25' : '&#x39;',
			'iconuntitled-26' : '&#x3a;',
			'iconuntitled-27' : '&#x3b;',
			'iconuntitled-28' : '&#x3c;',
			'iconuntitled-29' : '&#x3d;',
			'iconuntitled-30' : '&#x3e;',
			'iconuntitled-31' : '&#x3f;',
			'iconuntitled-32' : '&#x40;',
			'iconuntitled-33' : '&#x41;',
			'iconuntitled-34' : '&#x42;',
			'iconuntitled-35' : '&#x43;',
			'iconuntitled-36' : '&#x44;',
			'iconuntitled-37' : '&#x45;',
			'iconuntitled-38' : '&#x46;',
			'iconuntitled-39' : '&#x47;',
			'iconuntitled-40' : '&#x48;',
			'iconuntitled-41' : '&#x49;',
			'iconuntitled-42' : '&#x4a;',
			'iconuntitled-43' : '&#x4b;',
			'iconuntitled-44' : '&#x4c;',
			'iconuntitled-45' : '&#x4d;',
			'iconuntitled-46' : '&#x4e;',
			'iconuntitled-47' : '&#x4f;',
			'iconuntitled-48' : '&#x50;',
			'iconuntitled-49' : '&#x51;',
			'iconuntitled-50' : '&#x52;',
			'iconuntitled-51' : '&#x53;',
			'iconuntitled-52' : '&#x54;',
			'iconuntitled-53' : '&#x55;',
			'iconuntitled-54' : '&#x56;',
			'iconuntitled-55' : '&#x57;',
			'iconuntitled-56' : '&#x58;',
			'iconuntitled-57' : '&#x59;',
			'iconmodem' : '&#x5a;',
			'iconspeaker' : '&#x5b;',
			'iconpictures' : '&#x5c;',
			'iconeye' : '&#x5d;',
			'iconanalytics' : '&#x6f;',
			'iconcode' : '&#x5e;',
			'iconcabinet' : '&#x5f;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon');
		if (attr) {
			c = icons['icon' + attr];
			if (c) {
				attr = c;
			}
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};