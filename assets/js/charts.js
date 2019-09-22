var Charts = function() {"use strict";
	var lineChartHandler = function() {
		var options = {
			// Sets the chart to be responsive
			responsive: true,

			///Boolean - Whether grid lines are shown across the chart
			scaleShowGridLines: true,

			//String - Colour of the grid lines
			scaleGridLineColor: 'rgba(0,0,0,.05)',

			//Number - Width of the grid lines
			scaleGridLineWidth: 1,

			//Boolean - Whether the line is curved between points
			bezierCurve: true,

			//Number - Tension of the bezier curve between points
			bezierCurveTension: 0.4,

			//Boolean - Whether to show a dot for each point
			pointDot: true,

			//Number - Radius of each point dot in pixels
			pointDotRadius: 4,

			//Number - Pixel width of point dot stroke
			pointDotStrokeWidth: 1,

			//Number - amount extra to add to the radius to cater for hit detection outside the drawn point
			pointHitDetectionRadius: 20,

			//Boolean - Whether to show a stroke for datasets
			datasetStroke: true,

			//Number - Pixel width of dataset stroke
			datasetStrokeWidth: 2,

			//Boolean - Whether to fill the dataset with a colour
			datasetFill: true,

			// Function - on animation progress
			onAnimationProgress: function() {
			},

			// Function - on animation complete
			onAnimationComplete: function() {
			},

			//String - A legend template
			legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
		};
		var data = {
			labels: ["January", "February", "March", "April", "May", "June", "July"],
			datasets: [{
				label: "My First dataset",
				fillColor: "rgba(220,220,220,0.2)",
				strokeColor: "rgba(220,220,220,1)",
				pointColor: "rgba(220,220,220,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(220,220,220,1)",
				data: [65, 59, 80, 81, 56, 55, 40]
			}, {
				label: "My Second dataset",
				fillColor: "rgba(151,187,205,0.2)",
				strokeColor: "rgba(151,187,205,1)",
				pointColor: "rgba(151,187,205,1)",
				pointStrokeColor: "#fff",
				pointHighlightFill: "#fff",
				pointHighlightStroke: "rgba(151,187,205,1)",
				data: [28, 48, 40, 19, 86, 27, 90]
			}]
		};

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#lineChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var lineChart = new Chart(ctx).Line(data, options);
		//generate the legend
		var legend = lineChart.generateLegend();
		//and append it to your page somewhere
		$('#lineLegend').append(legend);
	};
	var barChartHandler = function() {
		var options = {

			// Sets the chart to be responsive
			responsive: true,

			//Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
			scaleBeginAtZero: true,

			//Boolean - Whether grid lines are shown across the chart
			scaleShowGridLines: true,

			//String - Colour of the grid lines
			scaleGridLineColor: "rgba(0,0,0,.05)",

			//Number - Width of the grid lines
			scaleGridLineWidth: 1,

			//Boolean - If there is a stroke on each bar
			barShowStroke: true,

			//Number - Pixel width of the bar stroke
			barStrokeWidth: 2,

			//Number - Spacing between each of the X value sets
			barValueSpacing: 5,

			//Number - Spacing between data sets within X values
			barDatasetSpacing: 1,

			//String - A legend template
			legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].fillColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
		};
		var data = {
			labels: ["January", "February", "March", "April", "May", "June", "July"],
			datasets: [{
				label: "My First dataset",
				fillColor: "rgba(220,220,220,0.5)",
				strokeColor: "rgba(220,220,220,0.8)",
				highlightFill: "rgba(220,220,220,0.75)",
				highlightStroke: "rgba(220,220,220,1)",
				data: [65, 59, 80, 81, 56, 55, 40]
			}, {
				label: "My Second dataset",
				fillColor: "rgba(151,187,205,0.5)",
				strokeColor: "rgba(151,187,205,0.8)",
				highlightFill: "rgba(151,187,205,0.75)",
				highlightStroke: "rgba(151,187,205,1)",
				data: [28, 48, 40, 19, 86, 27, 90]
			}]
		};

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#barChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var barChart = new Chart(ctx).Bar(data, options);
		;
		//generate the legend
		var legend = barChart.generateLegend();
		//and append it to your page somewhere
		$('#barLegend').append(legend);
	};
	var doughnutChartHandler = function() {
		var data = [{
			value: 300,
			color: '#F7464A',
			highlight: '#FF5A5E',
			label: 'Red'
		}, {
			value: 50,
			color: '#46BFBD',
			highlight: '#5AD3D1',
			label: 'Green'
		}, {
			value: 100,
			color: '#FDB45C',
			highlight: '#FFC870',
			label: 'Yellow'
		}];

		// Chart.js Options
		var options = {

			// Sets the chart to be responsive
			responsive: false,

			//Boolean - Whether we should show a stroke on each segment
			segmentShowStroke: true,

			//String - The colour of each segment stroke
			segmentStrokeColor: '#fff',

			//Number - The width of each segment stroke
			segmentStrokeWidth: 2,

			//Number - The percentage of the chart that we cut out of the middle
			percentageInnerCutout: 50, // This is 0 for Pie charts

			//Number - Amount of animation steps
			animationSteps: 100,

			//String - Animation easing effect
			animationEasing: 'easeOutBounce',

			//Boolean - Whether we animate the rotation of the Doughnut
			animateRotate: true,

			//Boolean - Whether we animate scaling the Doughnut from the centre
			animateScale: false,

			//String - A legend template
			legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'

		};

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#doughnutChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var doughnutChart = new Chart(ctx).Doughnut(data, options);
		;
		//generate the legend
		var legend = doughnutChart.generateLegend();
		//and append it to your page somewhere
		$('#doughnutLegend').append(legend);
	};
	var pieChartHandler = function() {
		// Chart.js Data
		var data = [{
			value: 300,
			color: '#F7464A',
			highlight: '#FF5A5E',
			label: 'Red'
		}, {
			value: 50,
			color: '#46BFBD',
			highlight: '#5AD3D1',
			label: 'Green'
		}, {
			value: 100,
			color: '#FDB45C',
			highlight: '#FFC870',
			label: 'Yellow'
		}];

		// Chart.js Options
		var options = {

			// Sets the chart to be responsive
			responsive: false,

			//Boolean - Whether we should show a stroke on each segment
			segmentShowStroke: true,

			//String - The colour of each segment stroke
			segmentStrokeColor: '#fff',

			//Number - The width of each segment stroke
			segmentStrokeWidth: 2,

			//Number - The percentage of the chart that we cut out of the middle
			percentageInnerCutout: 0, // This is 0 for Pie charts

			//Number - Amount of animation steps
			animationSteps: 100,

			//String - Animation easing effect
			animationEasing: 'easeOutBounce',

			//Boolean - Whether we animate the rotation of the Doughnut
			animateRotate: true,

			//Boolean - Whether we animate scaling the Doughnut from the centre
			animateScale: false,

			//String - A legend template
			legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'

		};

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#pieChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var pieChart = new Chart(ctx).Pie(data, options);
		;
		//generate the legend
		var legend = pieChart.generateLegend();
		//and append it to your page somewhere
		$('#pieLegend').append(legend);
	};
	var polarChartHandler = function() {
		// Chart.js Data
		var data = [{
			value: 300,
			color: '#F7464A',
			highlight: '#FF5A5E',
			label: 'Red'
		}, {
			value: 50,
			color: '#46BFBD',
			highlight: '#5AD3D1',
			label: 'Green'
		}, {
			value: 100,
			color: '#FDB45C',
			highlight: '#FFC870',
			label: 'Yellow'
		}, {
			value: 40,
			color: '#949FB1',
			highlight: '#A8B3C5',
			label: 'Grey'
		}, {
			value: 120,
			color: '#4D5360',
			highlight: '#616774',
			label: 'Dark Grey'
		}];

		// Chart.js Options
		var options = {

			// Sets the chart to be responsive
			responsive: false,

			//Boolean - Show a backdrop to the scale label
			scaleShowLabelBackdrop: true,

			//String - The colour of the label backdrop
			scaleBackdropColor: 'rgba(255,255,255,0.75)',

			// Boolean - Whether the scale should begin at zero
			scaleBeginAtZero: true,

			//Number - The backdrop padding above & below the label in pixels
			scaleBackdropPaddingY: 2,

			//Number - The backdrop padding to the side of the label in pixels
			scaleBackdropPaddingX: 2,

			//Boolean - Show line for each value in the scale
			scaleShowLine: true,

			//Boolean - Stroke a line around each segment in the chart
			segmentShowStroke: true,

			//String - The colour of the stroke on each segement.
			segmentStrokeColor: '#fff',

			//Number - The width of the stroke value in pixels
			segmentStrokeWidth: 2,

			//Number - Amount of animation steps
			animationSteps: 100,

			//String - Animation easing effect.
			animationEasing: 'easeOutBounce',

			//Boolean - Whether to animate the rotation of the chart
			animateRotate: true,

			//Boolean - Whether to animate scaling the chart from the centre
			animateScale: false,

			//String - A legend template
			legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
		};

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#polarChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var polarChart = new Chart(ctx).PolarArea(data, options);
		;
		//generate the legend
		var legend = polarChart.generateLegend();
		//and append it to your page somewhere
		$('#polarLegend').append(legend);
	};
	var radarChartHandler = function() {
		// Chart.js Data
    var data = {
        labels: ['Eating', 'Drinking', 'Sleeping', 'Designing', 'Coding', 'Cycling', 'Running'],
        datasets: [
          {
              label: 'My First dataset',
              fillColor: 'rgba(220,220,220,0.2)',
              strokeColor: 'rgba(220,220,220,1)',
              pointColor: 'rgba(220,220,220,1)',
              pointStrokeColor: '#fff',
              pointHighlightFill: '#fff',
              pointHighlightStroke: 'rgba(220,220,220,1)',
              data: [65, 59, 90, 81, 56, 55, 40]
          },
          {
              label: 'My Second dataset',
              fillColor: 'rgba(151,187,205,0.2)',
              strokeColor: 'rgba(151,187,205,1)',
              pointColor: 'rgba(151,187,205,1)',
              pointStrokeColor: '#fff',
              pointHighlightFill: '#fff',
              pointHighlightStroke: 'rgba(151,187,205,1)',
              data: [28, 48, 40, 19, 96, 27, 100]
          }
        ]
    };

    // Chart.js Options
   var options = {

        // Sets the chart to be responsive
        responsive: true,

        //Boolean - Whether to show lines for each scale point
        scaleShowLine: true,

        //Boolean - Whether we show the angle lines out of the radar
        angleShowLineOut: true,

        //Boolean - Whether to show labels on the scale
        scaleShowLabels: false,

        // Boolean - Whether the scale should begin at zero
        scaleBeginAtZero: true,

        //String - Colour of the angle line
        angleLineColor: 'rgba(0,0,0,.1)',

        //Number - Pixel width of the angle line
        angleLineWidth: 1,

        //String - Point label font declaration
        pointLabelFontFamily: '"Arial"',

        //String - Point label font weight
        pointLabelFontStyle: 'normal',

        //Number - Point label font size in pixels
        pointLabelFontSize: 10,

        //String - Point label font colour
        pointLabelFontColor: '#666',

        //Boolean - Whether to show a dot for each point
        pointDot: true,

        //Number - Radius of each point dot in pixels
        pointDotRadius: 3,

        //Number - Pixel width of point dot stroke
        pointDotStrokeWidth: 1,

        //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
        pointHitDetectionRadius: 20,

        //Boolean - Whether to show a stroke for datasets
        datasetStroke: true,

        //Number - Pixel width of dataset stroke
        datasetStrokeWidth: 2,

        //Boolean - Whether to fill the dataset with a colour
        datasetFill: true,

        //String - A legend template
        legendTemplate: '<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].strokeColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
    };

		// Get context with jQuery - using jQuery's .get() method.
		var ctx = $("#radarChart").get(0).getContext("2d");
		// This will get the first returned node in the jQuery collection.
		var radarChart = new Chart(ctx).Radar(data, options);
		;
		//generate the legend
		var legend = radarChart.generateLegend();
		//and append it to your page somewhere
		$('#radarLegend').append(legend);
	};
	return {
		//main function to initiate template pages
		init: function() {
			lineChartHandler();
			barChartHandler();
			doughnutChartHandler();
			pieChartHandler();
			polarChartHandler();
			radarChartHandler();
		}
	};
}();
