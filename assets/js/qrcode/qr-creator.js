const createQR = () => {
    const qr = document.querySelector(".qrcode");

    // Reset QR Code
    qr.innerHTML = "";

    // Clone current URL and remove tvembed
    const url = new URL(window.location.href);
    url.searchParams.delete('tvembed');

    // Generate QR
    new QRCode(qr, {
        text: url.toString(),
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
};

window.addEventListener('DOMContentLoaded', createQR);