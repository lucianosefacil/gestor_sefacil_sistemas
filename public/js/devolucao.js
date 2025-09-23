$('body').on('blur', '.value_unit', function() {
	
	$input_subtotal = $(this).closest('td').next().find('input');

	var value_unit = $(this).val();
	var qtd = $(this).closest('td').prev().find('input').val();

	var sub_total = convertMoedaToFloat(qtd) * convertMoedaToFloat(value_unit)
	$input_subtotal.val(convertFloatToMoeda(sub_total))

})

function convertFloatToMoeda(value) {
    return value.toLocaleString("pt-BR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function convertMoedaToFloat(value) {
    if (!value) {
        return 0;
    }

    var number_without_mask = value.replaceAll(".", "").replaceAll(",", ".");
    return parseFloat(number_without_mask.replace(/[^0-9\.]+/g, ""));
}

$('body').on('blur', '.vbc_icms', function() {
	
	$input_icms_value = $(this).closest('td').next().find('input');

	var value = $(this).val();
	var perc = $(this).closest('td').prev().find('input').val();

	var value_icms = convertMoedaToFloat(value) * (convertMoedaToFloat(perc)/100)
	$input_icms_value.val(convertFloatToMoeda(value_icms))

})

$('body').on('blur', '.vbc_pis', function() {
	
	$input_pis_value = $(this).closest('td').next().find('input');

	var value = $(this).val();
	var perc = $(this).closest('td').prev().find('input').val();

	var value_pis = convertMoedaToFloat(value) * (convertMoedaToFloat(perc)/100)
	$input_pis_value.val(convertFloatToMoeda(value_pis))

})

$('body').on('blur', '.vbc_cofins', function() {
	
	$input_cofins_value = $(this).closest('td').next().find('input');

	var value = $(this).val();
	var perc = $(this).closest('td').prev().find('input').val();

	var value_cofins = convertMoedaToFloat(value) * (convertMoedaToFloat(perc)/100)
	$input_cofins_value.val(convertFloatToMoeda(value_cofins))

})

$('body').on('blur', '.vbc_ipi', function() {
	
	$input_ipi_value = $(this).closest('td').next().find('input');

	var value = $(this).val();
	var perc = $(this).closest('td').prev().find('input').val();

	var value_ipi = convertMoedaToFloat(value) * (convertMoedaToFloat(perc)/100)
	$input_ipi_value.val(convertFloatToMoeda(value_ipi))

})


