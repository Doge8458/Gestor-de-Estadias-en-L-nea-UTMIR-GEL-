const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');
const lemaFooter = document.querySelector('.lema');

if (signUpButton && container && lemaFooter) {
    signUpButton.addEventListener('click', () => {
        container.classList.add('right-panel-active');
        lemaFooter.classList.add('naranja');
    });
}

if (signInButton && container && lemaFooter) {
    signInButton.addEventListener('click', () => {
        container.classList.remove('right-panel-active');
        lemaFooter.classList.remove('naranja');
    });
}

const currentYear = document.getElementById('currentYear');
if (currentYear) {
    currentYear.textContent = new Date().getFullYear();
}

const btnAviso = document.getElementById('btnAviso');
const modalAviso = document.getElementById('modalAviso');
const btnCerrarAviso = document.getElementById('btnCerrarAviso');

if (btnAviso && modalAviso) {
    btnAviso.addEventListener('click', () => modalAviso.classList.add('active'));
}

if (btnCerrarAviso && modalAviso) {
    btnCerrarAviso.addEventListener('click', () => modalAviso.classList.remove('active'));
}

fetch('api/estado_periodo.php')
    .then(response => response.json())
    .then(data => {
        if (!data.activo) {
            document.getElementById('pantalla-bloqueo').style.display = 'flex';
            document.getElementById('container').style.display = 'none';
            return;
        }

        document.getElementById('btnAviso').classList.remove('is-hidden');

        flatpickr('#calendario_alumno', {
            mode: 'range',
            inline: true,
            showMonths: 1,
            locale: 'es',
            defaultDate: [data.fecha_inicio, data.fecha_fin],
            clickOpens: false,
            onChange: function () { return false; }
        });

        const countDownDate = new Date(data.fecha_fin.replace(/-/g, '/')).getTime();

        setInterval(function () {
            const now = new Date().getTime();
            const distance = countDownDate - now;

            if (distance <= 0) {
                return;
            }

            document.getElementById('cd-al-dias').innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById('cd-al-horas').innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById('cd-al-mins').innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            document.getElementById('cd-al-segs').innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
        }, 1000);
    })
    .catch(error => console.error('Error validando periodo:', error));
