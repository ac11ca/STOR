$(document).ready(function(){
    var $linechart = $('.js-linechart');

    if($linechart.length > 0)
    {
        google.charts.load('current', {'packages':['line']});

        $linechart.each(function(){
            var $self = $(this);
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
              var params = $self.data('params');
              var data_rows = params.data || [];
              var data = new google.visualization.DataTable();
              if(params.data && params.data.length > 0) {
                  for(var i=0;i < params.data[0].length; i++)
                  {
                      if(params.coltypes[i]) {
                          data.addColumn(params.coltypes[i], params.columns[i]);
                      }
                  }
              }
              
              data.addRows(data_rows);

              var options = {
                chart: {
                  title: params.title
                },
                pointsVisibile: true,
                colors: params.color
              };

              var chart = new google.charts.Line($self[0]);

              chart.draw(data, options);
            }
        });
    }
});
