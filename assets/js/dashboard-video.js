var player;
const dashboardVideoDataElement = document.getElementById('dashboard-data');
const dashboardVideoData = dashboardVideoDataElement ? JSON.parse(dashboardVideoDataElement.textContent) : {};
var estadoVideoVisto = dashboardVideoData.videoVisto ? 1 : 0;

document.addEventListener('DOMContentLoaded', function () {
    const btnCerrarVideo = document.getElementById('btnCerrarVideo');
    const videoModal = document.getElementById('videoModal');

    if (btnCerrarVideo && videoModal) {
        btnCerrarVideo.addEventListener('click', () => videoModal.classList.remove('active'));
    }
});

function onYouTubeIframeAPIReady() {
    player = new YT.Player('youtubePlayer', {
        height: '360',
        width: '100%',
        videoId: 'ha5shSJ56Ac',
        playerVars: {
            controls: 0,
            disablekb: 1,
            rel: 0,
            modestbranding: 1
        },
        events: {
            onStateChange: onPlayerStateChange
        }
    });
}

function onPlayerStateChange(event) {
    if (event.data == YT.PlayerState.ENDED && estadoVideoVisto == 0) {
        document.getElementById('btnCerrarVideo').classList.remove('is-hidden');

        fetch('api/marcar_video.php', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                console.log('Respuesta del servidor:', data);
            })
            .catch(error => console.error('Error al guardar el estado del video:', error));

        estadoVideoVisto = 1;
    }
}

function abrirVideoTutorial() {
    document.getElementById('videoModal').classList.add('active');
}
