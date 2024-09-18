$(function() {
    $('.chart').each(function() {
        const ctx = $(this);

        var cType = ctx.data('type')
        var cDataset = ctx.data('dataset')
        var cOptions = ctx.data('options')

        new Chart(ctx, {
            type: cType,
            data: {
                labels: cDataset.labels,
                datasets: [{
                    label: cDataset.datasets[0].label,
                    data: cDataset.datasets[0].data,
                    borderWidth: cDataset.datasets[0].borderWidth,
                    borderColor: cDataset.datasets[0].borderColor,
                    backgroundColor: (cDataset.datasets[0].backgroundColor ? cDataset.datasets[0].backgroundColor : hexToRGB(cDataset.datasets[0].borderColor, 0.3)),
                    fill: cDataset.datasets[0].fill
                }]
            },
            options: cOptions
        });
    });
});

function hexToRGB(hex, alpha) {
    var r = parseInt(hex.slice(1, 3), 16),
        g = parseInt(hex.slice(3, 5), 16),
        b = parseInt(hex.slice(5, 7), 16);

    if (alpha) {
        return "rgba(" + r + ", " + g + ", " + b + ", " + alpha + ")";
    } else {
        return "rgb(" + r + ", " + g + ", " + b + ")";
    }
}