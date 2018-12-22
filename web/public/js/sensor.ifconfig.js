
window.monitorIfconfigChart = function(element) {
    var ctx = element.getContext('2d');
    var config = {
        type: 'line',
        data: {
            datasets: []
        },
        options: {
            legend: {
                display: true,
            },
            scales: {
                xAxes: [{
                    type: 'time',
                    display: true,
                    scaleLabel: {
                            display: true,
                            labelString: 'Time'
                    }
                }],
                yAxes: [{
                    stacked: true,
                    ticks: {
                        beginAtZero:true
                    },
                    scaleLabel: {
                            display: true,
                            labelString: '[Bytes]'
                    }
                }]
            },
            annotation: {
                annotations: []
            }
        }
    };
    window.memChart = new Chart(ctx, config);

    if (typeof window.monitorURL === 'undefined') {
        window.monitorURL = "https://monitor.web-d.be";
    }
    var api_url = window.monitorURL + "/api/sensor/"
            + window.monitorServerID + "/" + window.monitorServerToken
            + "/ifconfig";
    $.getJSON(api_url, function(data) {

        $.each(data, function(dataset){
            console.log(dataset);
            var new_dataset = {
                label: dataset.name,
                backgroundColor: window.chartColors.green,
                borderColor: window.chartColors.green,
                data: dataset.points
            };
            config.data.datasets.push(new_dataset);
        });

        window.memChart.update();
    });
};