$(document).ready(function () {
    verificar_escritura();
});

var verificar_escritura = function () {

    $( "#formulario_empleado" ).submit(function( event ) {
        event.preventDefault();

        var data = obtener_datos_formulario("formulario_empleado");

        $.ajax({
            url:'rutinas.php',
            type:'POST',
            data:{
                rutina:'login',
                data:JSON.stringify(data)
            },
            dataType:'json'

        }).done(function(result) {
            console.log(result);
            alert(result.mensaje);


        }).fail(function(){
            alert( "hubo un error con el ajax.");
        });



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