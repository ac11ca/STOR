$(document).ready(function(){
    var $report, title, chart_type;
    $report = $('#report_chart');
    title = $report.data('title');
    chart_type = $report.data('chart');
    console.log(chart_type);
    if($report.length > 0) {
        renderChart($report, title, chart_type);        
    }
});

function renderChart($chart, title, chart_type) {
    var data = $chart.data('dataset');    
    var chart_canvas = $chart[0].getContext('2d');
    var chart_object;
    var values = [];
    var labels = [];   
    var colors = []; 
    var dateObj = null;

    for(var i = 0; i < data.length; i++) {
        labels.push(data[i]['x']);
        values.push(data[i]['y']);
        colors.push(getRandomColor());
    }

    chart_canvas.canvas.width = 500;
    chart_canvas.canvas.height = 100;
    chart_object = new Chart(chart_canvas, {
        type: chart_type
        ,data: {
            labels: labels
            ,datasets: [{
                fill: true           
                ,data: values
                ,label: title
                ,backgroundColor: colors
                ,borderColor: colors
            }]   
        }
    });
}

function getRandomColor() {
    var letters = '0123456789ABCDEF';
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
