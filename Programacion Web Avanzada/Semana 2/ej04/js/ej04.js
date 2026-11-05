// Logica de JavaScript para el Reloj Analogico (Ejercicio 4)

function actualizarReloj() {
    const ahora = new Date();

    const segundos = ahora.getSeconds();
    const minutos = ahora.getMinutes();
    const horas = ahora.getHours();

    // Calculo de grados para cada manecilla
    // 360 grados / 60 segundos = 6 grados por segundo
    const gradosSegundos = segundos * 6;
    
    // 6 grados por minuto + un pequeño avance proporcional segun los segundos transcurridos
    const gradosMinutos = minutos * 6 + segundos * 0.1;
    
    // 360 grados / 12 horas = 30 grados por hora + avance proporcional según los minutos
    const gradosHoras = (horas % 12) * 30 + minutos * 0.5;

    // Aplicar las rotaciones mediante la propiedad transform de CSS
    document.getElementById('manecillaSegundo').style.transform = `rotate(${gradosSegundos}deg)`;
    document.getElementById('manecillaMinuto').style.transform = `rotate(${gradosMinutos}deg)`;
    document.getElementById('manecillaHora').style.transform = `rotate(${gradosHoras}deg)`;

    // Opcional: Actualizar tambien el reloj digital inferior en formato legible
    const formatoHora = String(horas).padStart(2, '0');
    const formatoMinutos = String(minutos).padStart(2, '0');
    const formatoSegundos = String(segundos).padStart(2, '0');
    
    document.getElementById('relojDigital').innerText = `${formatoHora}:${formatoMinutos}:${formatoSegundos}`;
}

// Ejecutar la función cada 1000 milisegundos (1 segundo)
setInterval(actualizarReloj, 1000);

// Llamarla inmediatamente al cargar la página para evitar retrasos de 1 segundo
actualizarReloj();