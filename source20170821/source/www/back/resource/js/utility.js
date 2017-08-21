var disable_alarm = false;
var g_closeText="閉じる";
var g_prevText="先月";
var g_nextText="翌月";
var g_currentText="今日";
var g_monthNames=["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"];
var g_monthNamesShort=["1月","2月","3月","4月","5月","6月","7月","8月","9月","10月","11月","12月"];
var g_dayNames=["日曜日","月曜日","火曜日","水曜日","木曜日","金曜日","土曜日"];
var g_dayNamesShort=["日","月","火","水","木","金","土"];
var g_dayNamesMin=["日","月","火","水","木","金","土"];
var g_weekHeader="週";
var g_submitting="しばらく、お待ちください。";

// customizing buttong click
$.fn.aclick = function(callback) {
	$(this).click(function(e) {
		if ($(this).hasAttr('disabled')) return;
		this.cbClick = callback;
		this.cbClick(e);
	});
};
$.fn.aenable = function() {
	$(this).removeAttr('disabled');
};
$.fn.adisable = function() {
	$(this).attr('disabled', 'disabled');
};
$.fn.hasAttr = function(attr) {
	var obj = $(this)[0];
	return obj.hasAttribute ? obj.hasAttribute(attr) : obj.getAttribute(attr);
}
$.fn.isChecked = function() {
	var obj = $(this)[0];
	return obj.checked;
};

var isIE67 = false;

$(function() {
	isIE67 = $.browser.msie && $.browser.version <= 7;

	/*
	 * JQUERY UI DATE
	 * Dependency: js/libs/jquery-ui-1.10.3.min.js
	 * Usage:
	 */
	if ($.fn.datepicker) {
		$('.datepicker').each(function() {

			$this = $(this);
			var dataDateFormat = $this.attr('data-dateformat') || 'yy-mm-dd';

			$this.datepicker({
				dateFormat : dataDateFormat,
				closeText:g_closeText,
				prevText:g_prevText,
				nextText:g_nextText,
				currentText:g_currentText,
				monthNames:g_monthNames,
				monthNamesShort:g_monthNamesShort,
				dayNames:g_dayNames,
				dayNamesShort:g_dayNamesShort,
				dayNamesMin:g_dayNamesMin,
				weekHeader:g_weekHeader
			});
		})
	}
	/*
	 * MASKING
	 * Dependency: js/plugin/masked-input/
	 */
	if ($.fn.mask) {
		$('[data-mask]').each(function() {
			$this = $(this);
			var mask = $this.attr('data-mask') || 'error...', mask_placeholder = $this.attr('data-mask-placeholder') || 'X';

			$this.mask(mask, {
				placeholder : mask_placeholder
			});
		})
	}

	init_data_sort();

	if ($.fn.autocomplete) {
		$('.input-visitor').autocomplete("users/visitor_autocomplete", {
			mustMatch: true,
			autoFill: true
		}).result(function(e, result) {
			var val = result != null ? result[1] : "";
			$('#' + $(this).attr('visitor_id')).val(val);
		});
		$('.input-prisoner').autocomplete("users/prisoner_autocomplete", {
			mustMatch: true,
			autoFill: true
		}).result(function(e, result) {
			var val = result != null ? result[1] : "";
			$('#' + $(this).attr('prisoner_id')).val(val);
		});
	}

	if ($.fn.fancybox)
	{
		$(".fancybox").each(function() {
			$this = $(this);
			width = $this.attr('fancy-width');
			width = width != undefined ? toInt(width) : undefined;
			height = $this.attr('fancy-height');
			height = height != undefined ? toInt(height) : undefined;
			$this.fancybox({
				'type' : 'iframe',
				'width' : width,
				'height' : height
			});
		});
	}

	$('#btnClearSearch').click(function() {
		$('.navbar-form').find('input').val('');
		$('.navbar-form').find('select').val('');
		$('#list_form').submit();
	});

	if ($.fn.nivoSlider)
	{
		$('.nivoSlider').nivoSlider({ 
			pauseTime: parseInt(10000), pauseOnHover: true, 
			effect: 'random', controlNav: true, captionOpacity: .7, directionNavHide: true, 
			controlNavThumbs:false, controlNavThumbsFromRel:false, boxCols:8, boxRows:4, 
			afterLoad: function(){ 
				$('#slider_loading').css('display', 'none');
				$('#slider_wrapper').css('visibility', 'visible');
			} 
		});
	}
	
	if ($.fn.cycle) {
		$('.cyclebar ul').cycle({
	        fx: "scrollDown",
	        easing: "easeOutCubic",
	        speed: 600,
	        timeout: 5000,
			height: 25
	    	});		
	}

	$('.article .rating input').click(function() {
		var _url;
		var $this = $(this);
		var rating = $this.val();
		var _option_id = "";
		article_id = $(this).attr('article_id');
		if (article_id != undefined)
		{
			_url = "articles/rating_ajax/" + article_id + "/" + rating;
			_option_id = '#rating_' + article_id + '_';
		}
		else {
			site_id = $(this).attr('site_id');
			if (site_id != undefined)
			{
				_url = "sites/rating_ajax/" + site_id + "/" + rating;
				_option_id = '#rating_' + site_id + '_';
			}
			else
				return;
		}

		$.ajax({
			url : _url,
			type : "post",
			dataType : 'json',
			success : function(data){
				if (data.err_code == 0)
				{
					r = data.rating;
					$this.removeAttr('checked');
					$(_option_id + r).attr('checked', 'checked');
				}
			},
			error : function() {
			},
			complete : function() {
			}
		});
	});

	$('.forum .rating input').click(function() {
		var _url;
		var $this = $(this);
		var rating = $this.val();
		var _option_id = "";
		forum_id = $(this).attr('forum_id');
		if (forum_id != undefined)
		{
			_url = "forums/rating_ajax/" + forum_id + "/" + rating;
			_option_id = '#rating_' + forum_id + '_';
		}
		else {
			site_id = $(this).attr('site_id');
			if (site_id != undefined)
			{
				_url = "sites/rating_ajax/" + site_id + "/" + rating;
				_option_id = '#rating_' + site_id + '_';
			}
			else
				return;
		}

		$.ajax({
			url : _url,
			type : "post",
			dataType : 'json',
			success : function(data){
				if (data.err_code == 0)
				{
					r = data.rating;
					$this.removeAttr('checked');
					$(_option_id + r).attr('checked', 'checked');
				}
			},
			error : function() {
			},
			complete : function() {
			}
		});
	});

	$('.qa .rating input').click(function() {
		var _url;
		var $this = $(this);
		var rating = $this.val();
		var _option_id = "";
		qa_id = $(this).attr('qa_id');
		if (qa_id != undefined)
		{
			_url = "qas/rating_ajax/" + qa_id + "/" + rating;
			_option_id = '#rating_' + qa_id + '_';
		}
		else {
			site_id = $(this).attr('site_id');
			if (site_id != undefined)
			{
				_url = "sites/rating_ajax/" + site_id + "/" + rating;
				_option_id = '#rating_' + site_id + '_';
			}
			else
				return;
		}

		$.ajax({
			url : _url,
			type : "post",
			dataType : 'json',
			success : function(data){
				if (data.err_code == 0)
				{
					r = data.rating;
					$this.removeAttr('checked');
					$(_option_id + r).attr('checked', 'checked');
				}
			},
			error : function() {
			},
			complete : function() {
			}
		});
	});
	
	$('.help').tooltip();

	if (!disable_alarm)
	{
		set_access();
	}
	
	// reset bookmark url
	$('a').each(function() {
		var href = $(this).attr('href');
		
		if (href != null && href.indexOf('#') == 0 && href.length > 1) {
			$(this).attr('href', document.location.href + href);
		}
	});
});
	
// custom validator
jQuery.validator.addMethod("bigThan", function(value, element, param) {
	var target=$(param);
	if(this.settings.onfocusout){
		target.unbind(".validate-bigThan").bind("blur.validate-bigThan",function(){$(element).valid();});
	}
	return value > target.val();
});
jQuery.validator.addMethod("smallThan", function(value, element, param) {
	var target=$(param);
	if(this.settings.onfocusout){
		target.unbind(".validate-bigThan").bind("blur.validate-bigThan",function(){$(element).valid();});
	}
	return value < target.val();
});
jQuery.validator.addMethod("imagefile", function(value, element, param) {
	var found = value.match(/\.(jpg|jpeg|png)$/gi);
	return found != null;
});
jQuery.validator.addMethod("pwd_min_length", function(value, element, param) {
	if (value == "")
		return true;
	else
		return value.length >= param;
});
jQuery.validator.addMethod("pwd_strength", function(value, element, param) {
	if (value == "")
		return true;
	else {
		var e = value.match(/([A-Z])/gi);
		var n = value.match(/([0-9])/gi);
		var s = value.match(/([^A-Z0-9])/gi);
		return e != null && n != null && s != null;
	}
});
jQuery.validator.addMethod("editor_required", function(value, element, param) {
	var editor = eval("CKEDITOR.instances." + param);
	if (editor != null)
	{
		return editor.getData() != "";
	}
	else {
		return $('#' + param).val();
	}
});

jQuery.validator.addMethod('unique_email', function(email, element) {
	var ret = false;
	var user_id = $('#user_id').val();
	var user_type = $('#user_type').val();

	if (user_type == 1)
		return true;

	if (user_id == undefined)
		user_id = "";

	$.ajax({
		async : false,
		url :"common/check_email_ajax/" + encodeURI(email) + "/" + user_id,
		type : "post",
		dataType : 'json',
		success : function(r) {
			if (r.err_code == 0)
				ret = r.ret;
		},
		error : function() {
		},
		complete : function() {
		}
	});

	return ret;
}, "このメールアドレスは利用しています。");

function clearValidate(form)
{
	$(form + ' .control-group .help-block').remove();
	$(form + ' .control-group.has-error').removeClass('has-error');
	$(form + ' .control-group.has-success').removeClass('has-success');
}

function getValidationRules () {
	var custom = {
		focusCleanup: false,
		
		wrapper: 'div',
		errorElement: 'span',

		highlight: function(element) {
			$(element).parents ('.control-group').removeClass ('success').addClass('error');
		},
		success: function(element) {
			$(element).parents ('.control-group').removeClass ('error').addClass('success');
			$(element).parents ('.control-group.success').find('.help-block').remove();
		},
		errorPlacement: function(error, element) {
			error.html("<i class='fa fa-warning'></i> " + error.html());
			error.addClass('help-block');
			var d = element.parents('.control-group').find('>div:last');
			error.appendTo(d);
		}		
	};

	return custom;
}

function init_data_sort()
{
	$('[data-sort]').click(function() {
		var field = $(this).attr('data-sort');
		var f = $('#sort_field').val();
		if (f == field)
		{
			var o = $('#sort_order').val();
			if (o == "" || o == "DESC")
				$('#sort_order').val("ASC");
			else
				$('#sort_order').val("DESC");
		}
		else {
			$('#sort_field').val(field);
			$('#sort_order').val('ASC');
		}
		$(this).parents('form').submit();
	});
}

function alertBox(title, message, callback, tout)
{
	if (tout == null)
		tout = 2000;
	$.smallBox({
		title : title,
		content : message,
		color : "#3ca0ef",
		timeout: tout,
		icon : "fa fa-check-circle"
	}, function() { if (callback != null) callback(); });
}

function errorBox(title, message, callback, tout)
{
	if (tout == null)
		tout = 10000;
	$.smallBox({
		title : title,
		content : message,
		color : "#e35555",
		timeout: tout,
		icon : "fa fa-exclamation-triangle"
	}, function() { if (callback != null) callback(); });
}

function confirmBox(title, message, onYes, onNo)
{
	$.SmartMessageBox({
		title : title,
		content : message,
		buttons : '[いいえ][はい]'
	}, function(ButtonPressed) {
		if (ButtonPressed == "はい" && onYes != null) {
			onYes();
		}
		if (ButtonPressed == "いいえ" && onNo != null) {
			onNo();
		}
	});
}

function confirmInputBox(title, message, placeholder, onYes, onNo)
{
	$.SmartMessageBox({
		title : title,
		content : message,
		input : "text",
		placeholder : placeholder,
		buttons : '[いいえ][はい]'
	}, function(ButtonPressed, Value) {
		if (ButtonPressed == "はい" && onYes != null) {
			onYes(Value);
		}
		if (ButtonPressed == "いいえ" && onNo != null) {
			onNo(Value);
		}
	});
}

function confirmSelectBox(title, message, placeholder, options, onYes, onNo)
{
	$.SmartMessageBox({
		title : title,
		content : message,
		input : "select",
		options : options,
		placeholder : placeholder,
		buttons : '[いいえ][はい]'
	}, function(ButtonPressed, Value) {
		if (ButtonPressed == "はい" && onYes != null) {
			onYes(Value);
		}
		if (ButtonPressed == "いいえ" && onNo != null) {
			onNo(Value);
		}
	});
}

function confirmSelectInputBox(title, message, placeholder, options, onYes, onNo)
{
	$.SmartMessageBox({
		title : title,
		content : message,
		input : "select|input",
		options : options,
		placeholder : placeholder,
		buttons : '[いいえ][はい]'
	}, function(ButtonPressed, Value) {
		if (ButtonPressed == "はい" && onYes != null) {
			onYes(Value);
		}
		if (ButtonPressed == "いいえ" && onNo != null) {
			onNo(Value);
		}
	});
}

/* Date Formatting
-----------------------------------------------------------------------------*/
// TODO: use same function formatDate(date, [date2], format, [options])
var date_defaults = {
	// time formats
	titleFormat: {
		month: 'MMMM yyyy',
		week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
		day: 'dddd, MMM d, yyyy'
	},
	columnFormat: {
		month: 'ddd',
		week: 'ddd M/d',
		day: 'dddd M/d'
	},
	timeFormat: { // for event elements
		'': 'hh:mm' // default
	},
	
	// locale
	isRTL: false,
	firstDay: 0,
	monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
	monthNamesShort: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
	dayNames: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
	dayNamesShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
	buttonText: {
		prev: '&nbsp;&#9668;&nbsp;',
		next: '&nbsp;&#9658;&nbsp;',
		prevYear: '&nbsp;&lt;&lt;&nbsp;',
		nextYear: '&nbsp;&gt;&gt;&nbsp;',
		today: 'today',
		month: 'month',
		week: 'week',
		day: 'day'
	}	
};

function formatDate(date, format, options) {
	return formatDates(date, null, format, options);
}


function formatDates(date1, date2, format, options) {
	options = options || date_defaults;
	var date = date1,
		otherDate = date2,
		i, len = format.length, c,
		i2, formatter,
		res = '';
	for (i=0; i<len; i++) {
		c = format.charAt(i);
		if (c == "'") {
			for (i2=i+1; i2<len; i2++) {
				if (format.charAt(i2) == "'") {
					if (date) {
						if (i2 == i+1) {
							res += "'";
						}else{
							res += format.substring(i+1, i2);
						}
						i = i2;
					}
					break;
				}
			}
		}
		else if (c == '(') {
			for (i2=i+1; i2<len; i2++) {
				if (format.charAt(i2) == ')') {
					var subres = formatDate(date, format.substring(i+1, i2), options);
					if (toInt(subres.replace(/\D/, ''), 10)) {
						res += subres;
					}
					i = i2;
					break;
				}
			}
		}
		else if (c == '[') {
			for (i2=i+1; i2<len; i2++) {
				if (format.charAt(i2) == ']') {
					var subformat = format.substring(i+1, i2);
					var subres = formatDate(date, subformat, options);
					if (subres != formatDate(otherDate, subformat, options)) {
						res += subres;
					}
					i = i2;
					break;
				}
			}
		}
		else if (c == '{') {
			date = date2;
			otherDate = date1;
		}
		else if (c == '}') {
			date = date1;
			otherDate = date2;
		}
		else {
			for (i2=len; i2>i; i2--) {
				if (formatter = dateFormatters[format.substring(i, i2)]) {
					if (date) {
						res += formatter(date, options);
					}
					i = i2 - 1;
					break;
				}
			}
			if (i2 == i) {
				if (date) {
					res += c;
				}
			}
		}
	}
	return res;
};


var dateFormatters = {
	s	: function(d)	{ return d.getSeconds() },
	ss	: function(d)	{ return zeroPad(d.getSeconds()) },
	m	: function(d)	{ return d.getMinutes() },
	mm	: function(d)	{ return zeroPad(d.getMinutes()) },
	h	: function(d)	{ return d.getHours() % 12 || 12 },
	hh	: function(d)	{ return zeroPad(d.getHours() % 12 || 12) },
	H	: function(d)	{ return d.getHours() },
	HH	: function(d)	{ return zeroPad(d.getHours()) },
	d	: function(d)	{ return d.getDate() },
	dd	: function(d)	{ return zeroPad(d.getDate()) },
	ddd	: function(d,o)	{ return o.dayNamesShort[d.getDay()] },
	dddd: function(d,o)	{ return o.dayNames[d.getDay()] },
	M	: function(d)	{ return d.getMonth() + 1 },
	MM	: function(d)	{ return zeroPad(d.getMonth() + 1) },
	MMM	: function(d,o)	{ return o.monthNamesShort[d.getMonth()] },
	MMMM: function(d,o)	{ return o.monthNames[d.getMonth()] },
	yy	: function(d)	{ return (d.getFullYear()+'').substring(2) },
	yyyy: function(d)	{ return d.getFullYear() },
	t	: function(d)	{ return d.getHours() < 12 ? 'a' : 'p' },
	tt	: function(d)	{ return d.getHours() < 12 ? 'am' : 'pm' },
	T	: function(d)	{ return d.getHours() < 12 ? 'A' : 'P' },
	TT	: function(d)	{ return d.getHours() < 12 ? 'AM' : 'PM' },
	u	: function(d)	{ return formatDate(d, "yyyy-MM-dd'T'HH:mm:ss'Z'") },
	S	: function(d)	{
		var date = d.getDate();
		if (date > 10 && date < 20) {
			return 'th';
		}
		return ['st', 'nd', 'rd'][date%10-1] || 'th';
	}
};

function zeroPad(n) {
	return (n < 10 ? '0' : '') + n;
}

function diff_times(start/*YY:mm*/, end/*YY:mm*/) {
	try
	{
		if (start == "" || end == "")
		{
			return '';
		}
		var s = start.split(':');
		var e = end.split(':');
		s = toInt(s[0], 10) * 60 + toInt(s[1], 10);
		e = toInt(e[0], 10) * 60 + toInt(e[1], 10);
		d = e - s;
		return zeroPad(Math.floor(d / 60)) + ":" + zeroPad(d % 60);
	}
	catch (e)
	{
	}
}

function goto_url(url)
{
	base_url = $('base').attr('href');
	if (url.charAt(0) != '/' && url.charAt(0) != '.')
	{
		document.location = base_url + url;
	}
}

/* access related */
var first_access = 1;
function set_access()
{
	/*
	operation = $('h2:first').text();

	$.ajax({
		url :"batch/access/" + first_access,
		type : "post",
		dataType : 'json',
		data : {
			operation : operation,
			url: document.location.href
		},
		success : function(ret) {
			if (ret.err_code == 0 && ret.alerts != null && ret.alerts.length > 0)
			{
				for (i = 0; i < ret.alerts.length; i ++)
				{
					msg = ret.alerts[i];
					alertBox(msg.title, msg.body, null, 30000);
				}
			}
		},
		error : function() {
		},
		complete : function() {
		}
	});

	if (first_access == 1)
	{
		first_access = 0;
	}
	setTimeout("set_access()", 60000);
	*/
}

/* get server time now */
function get_servertime(callback) {
	$.ajax({
		url :"common/now_ajax",
		type : "post",
		dataType : 'json',
		success : function(ret) {
			if (ret.err_code == 0)
			{
				callback(ret.now);
			}
		},
		error : function() {
		},
		complete : function() {
		}
	});
}

var first_load = true;
function page_refresh(refresh_url, refresh_obj, callback, step) {
	if (first_load == false)
	{
		$.ajax({
			url : refresh_url,
			type : "post",
			success : function(data){ 
				$(refresh_obj).html(data);
				eval(callback);
			},
			error : function() {
			},
			complete : function() {
			}
		}); 
	}
	else {
		eval(callback);
	}
	first_load = false;
	if (step == undefined)
	{
		step = 10000;
	}
	else {
		step = step * 1000;
	}
	setTimeout("page_refresh('" + refresh_url + "','" + refresh_obj + "','" + callback + "')", step);
}

function toInt(n)
{
	return parseInt(n, 10);
}

var maskTimeout = null;
function showMask(once, msg)
{
	if (msg == null)
		msg = g_submitting;

	if (once == null)
	{
		maskTimeout = setTimeout("showMask(true)", 1000);
	}
	else {
		var mask = $('<table class="submit-mask"><tr><td align="center"><h2>' + msg + '</h2></td></tr></table>');
		mask.width($(window).width());
		mask.height($(window).height());
		$('body').append(mask);
	}
}

function hideMask()
{
	if (maskTimeout) {
		clearTimeout(maskTimeout);
		maskTimeout = null;
	}
	$('.submit-mask').remove();
}
