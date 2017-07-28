$(document).ready(function() {

// Для работы с куки используется плагин jquery.cookie.js
// Перевод лежит в JSON locale.js

$(function() {

//ПЕРЕКЛЮЧЕНИЕ ЯЗЫКА
$('.lang').click(function(e) {
	e.preventDefault();
	//переключение локали
	var lang = $(this).attr('id');
	$.cookie('curLang', lang);

//правим перевод placeholder
if (lang === 'ukr') {
	$('.card-wrap_card_input__text').attr('placeholder', 'НОМЕР КАРТКИ');
} else if (lang === 'eng') {
	$('.card-wrap_card_input__text').attr('placeholder', 'CARD NUMBER');
} else {
	$('.card-wrap_card_input__text').attr('placeholder', 'НОМЕР КАРТЫ');
}	

//перевод
	$('.translate-it').each(function(){
		$(this).text(arrLang[lang][$(this).attr('key')]);
	});
//end перевод

//стилевое переключение
	$('.lang-menu').find('.lang').removeClass('active');
	$(this).addClass('active');
//end стилевое переключение
});
//END ПЕРЕКЛЮЧЕНИЕ ЯЗЫКА
});

(function() {
// Проверка есть ли что в куки
// если в куки ничего нет
// то пишем в куку активный пункт меню

	if ($.cookie('curLang') === undefined) {
		var lang = $('.active').attr('id');
		$.cookie('curLang', lang);

//правим перевод placeholder
if (lang === 'ukr') {
	$('.card-wrap_card_input__text').attr('placeholder', 'НОМЕР КАРТКИ');
} else if (lang === 'eng') {
	$('.card-wrap_card_input__text').attr('placeholder', 'CARD NUMBER');
} else {
	$('.card-wrap_card_input__text').attr('placeholder', 'НОМЕР КАРТЫ');
}

	} else{
		lang = $.cookie('curLang');

//стилевое переключение
		$('.lang-menu').find('.lang').removeClass('active');
		$('#'+lang).addClass('active');
//end стилевое переключение

//правим перевод placeholder
if (lang === 'ukr') {
	$('.card-wrap_card_input__text').text('НОМЕР КАРТКИ');
} else if (lang === 'eng') {
	$('.card-wrap_card_input__text').text('CARD NUMBER');
} else {
	$('.card-wrap_card_input__text').text('НОМЕР КАРТЫ');
}	
	};
//end проверка есть ли что в куки

//перевод на лету
	$('.translate-it').each(function(){
		$(this).text(arrLang[lang][$(this).attr('key')]);
	});
//end перевод на лету

})();



});