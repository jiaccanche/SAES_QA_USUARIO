$(document).ready(function () {
    verificar_escritura();
});

var verificar_escritura = function () {

    $( "#formulario_empleado" ).submit(function( event ) {
        event.preventDefault();

        var data = obtener_datos_formulario("#formulario_empleado");



    });

};

var obtener_datos_formulario = function (id) {
    var inputs_form = $("#"+id+" :input");

    var data = {};
    inputs_form.each(function () {
        data[this.name] = $(this).val();
    });

    return data;
};

var realizar_;