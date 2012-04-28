var $template;
$(function(){
	$('.tabs').tabs();
	$template = $('#row-template').detach();
	$("input:submit, input:button, a.button").button();
	$("button.add").button({
		icons: {
			primary: 'ui-icon-plusthick'
		}
	});
	$("button.play").button({
		icons: {
			primary: 'ui-icon-play'
		}
	});
	$('#add-row').click(function(event) {
		event.preventDefault();
		newRow();
		zebra_table('#captures');
	});
	zebra_table('#captures');

	$("#nice-slider").slider({
		value:0,
		min: -20,
		max: 20,
		step: 1,
		slide: function(event, ui) {
			value = ui.value;
			if (value > 0) {
				color = '#ff6f00';
				$('#simultaneous-input').val(value);
				$('#delay-input').val('0');
			} else if (value == 0) {
				color = '#000';
			} else {
				value = 0 - value * 5;
				$('#delay-input').val(value);
				$('#simultaneous-input').val('0');
				color = '#006fff';
			}
			$('#nice-visible').html(value).css('color', color);

			//$( "#amount" ).val( "$" +  );
		}
	});
	$( "#amount" ).val( "$" + $( "#slider" ).slider( "value" ) );

	$('#method_radios input').click(function(event) {
		if (event.currentTarget.value == 'crawl') {
			$('.handle-increment').hide();
			$('.handle-crawl').fadeIn();
		} else if (event.currentTarget.value == 'increment') {
			$('.handle-crawl').hide();
			$('.handle-increment').fadeIn();
		}
	});
	$('#output_radios input').click(function(event) {
		if (event.currentTarget.value == 'mysql') {
			$('.handle-file').hide();
			$('.handle-mysql').fadeIn();
		} else if (event.currentTarget.value == 'display') {
			$('.handle-file').hide();
			$('.handle-mysql').hide();
		} else { // file
			$('.handle-file').fadeIn();
			$('.handle-mysql').hide();
		}
	});
	//$('input[type=checkbox]').button();
	initUI('');
});

function initUI(parentSelector) {
	$(parentSelector + " button.trash").button({
		icons: {
			primary: 'ui-icon-trash'
		}
	});
	$(parentSelector + " button.trash").click(function(event) {
		event.preventDefault();
		$(event.currentTarget).parents("tr").remove();
		zebra_table('#captures');
	});
	$(parentSelector + " .radios").buttonset();
}

function newRow() {
	var id = Math.floor(Math.random() * 9999999);
	var $newRowElement = $template.clone();
	$newRowElement[0].id += '-' + id;
	$newRowElement.find('*').each(function() {
		//console.log(this);
		if (this.className == 'finite') {
			this.value = id;
		} else {
			if (this.id)
				this.id += '-' + id;
			if (this.htmlFor)
				this.htmlFor += '-' + id;
			if (this.name)
				this.name += '-' + id;
		}
	});
	$newRowElement.appendTo('#captures');
	initUI('#row-template-'+id);
	$("#capture-A-"+id).click();
}

function zebra_table(table) {
	$(table + ' tr:even').removeClass('odd').addClass('even');
	$(table + ' tr:odd').removeClass('even').addClass('odd');
}
