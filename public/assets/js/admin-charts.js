function drawLineChart(canvasId, labels, values) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const pad = { top: 14, right: 14, bottom: 26, left: 14 };
    const plotW = w - pad.left - pad.right;
    const plotH = h - pad.top - pad.bottom;
    const max = Math.max(...values) * 1.15 || 1;
    const stepX = plotW / (labels.length - 1);
    const color = '#002045';

    ctx.strokeStyle = '#eef0f5';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (plotH / 4) * i;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(w - pad.right, y); ctx.stroke();
    }

    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.lineTo(pad.left + stepX * (values.length - 1), pad.top + plotH);
    ctx.lineTo(pad.left, pad.top + plotH);
    ctx.closePath();
    const grad = ctx.createLinearGradient(0, pad.top, 0, pad.top + plotH);
    grad.addColorStop(0, color + '33');
    grad.addColorStop(1, color + '00');
    ctx.fillStyle = grad;
    ctx.fill();

    ctx.beginPath();
    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.strokeStyle = color;
    ctx.lineWidth = 2.2;
    ctx.lineJoin = 'round';
    ctx.stroke();

    values.forEach((v, i) => {
        const x = pad.left + stepX * i;
        const y = pad.top + plotH - (v / max) * plotH;
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fillStyle = '#fff';
        ctx.fill();
        ctx.lineWidth = 2;
        ctx.strokeStyle = color;
        ctx.stroke();
    });

    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px Inter, sans-serif';
    ctx.textAlign = 'center';
    labels.forEach((lbl, i) => {
        const x = pad.left + stepX * i;
        ctx.fillText(lbl, x, h - 6);
    });
}
    function drawDonut(canvasId, value, max) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const cx = w / 2, cy = h / 2, r = Math.min(w, h) / 2 - 8;
    const pct = Math.max(0, Math.min(1, value / (max || 1)));

    ctx.lineWidth = 10;
    ctx.strokeStyle = '#eef0f5';
    ctx.beginPath();
    ctx.arc(cx, cy, r, 0, Math.PI * 2);
    ctx.stroke();

    ctx.lineWidth = 10;
    ctx.strokeStyle = '#002045';
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.arc(cx, cy, r, -Math.PI / 2, -Math.PI / 2 + pct * Math.PI * 2);
    ctx.stroke();
}
function drawBarChart(canvasId, labels, values) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = w * dpr; canvas.height = h * dpr;
    ctx.scale(dpr, dpr);
    ctx.clearRect(0, 0, w, h);

    const pad = { top: 14, right: 14, bottom: 26, left: 14 };
    const plotW = w - pad.left - pad.right;
    const plotH = h - pad.top - pad.bottom;
    const max = Math.max(...values) * 1.15 || 1;
    const gap = 14;
    const barW = (plotW / labels.length) - gap;

    ctx.strokeStyle = '#eef0f5';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (plotH / 4) * i;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(w - pad.right, y); ctx.stroke();
    }

    values.forEach((v, i) => {
        const x = pad.left + i * (barW + gap) + gap / 2;
        const barH = (v / max) * plotH;
        const y = pad.top + plotH - barH;
        ctx.fillStyle = '#3b6090';
        ctx.beginPath();
        ctx.roundRect(x, y, barW, barH, 4);
        ctx.fill();
    });

    ctx.fillStyle = '#94a3b8';
    ctx.font = '12px Inter, sans-serif';
    ctx.textAlign = 'center';
    labels.forEach((lbl, i) => {
        const x = pad.left + i * (barW + gap) + gap / 2 + barW / 2;
        ctx.fillText(lbl, x, h - 6);
    });
}
