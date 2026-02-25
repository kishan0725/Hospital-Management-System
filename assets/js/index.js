'use strict';
var Index = function() {
	var chart1Handler = function() {
		var data = {
			labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
			datasets: [{
				label: 'My First dataset',
				fillColor: 'rgba(220,220,220,0.2)',
				strokeColor: 'rgba(220,220,220,1)',
				pointColor: 'rgba(220,220,220,1)',
				pointStrokeColor: '#fff',
				pointHighlightFill: '#fff',
				pointHighlightStroke: 'rgba(220,220,220,1)',
				data: [65, 59, 80, 81, 56, 55, 40, 84, 64, 120, 132, 87]
			}, {
				label: 'My Second dataset',
				fillColor: 'rgba(151,187,205,0.2)',
				strokeColor: 'rgba(151,187,205,1)',
				pointColor: 'rgba(151,187,205,1)',
				pointStrokeColor: '#fff',
				pointHighlightFill: '#fff',
				pointHighlightStroke: 'rgba(151,187,205,1)',
				data: [28, 48, 40, 19, 86, 27, 90, 102, 123, 145, 60, 161]
			}]
		};

		var options = {

			maintainAspectRatio: false,


			responsive: true,


			scaleShowGridLines: true,


			scaleGridLineColor: 'rgba(0,0,0,.05)',


			scaleGridLineWidth: 1,


			bezierCurve: false,


			bezierCurveTension: 0.4,


			pointDot: true,


			pointDotRadius: 4,


			pointDotStrokeWidth: 1,


			pointHitDetectionRadius: 20,


			datasetStroke: true,


			datasetStrokeWidth: 2,


			datasetFill: true,


			onAnimationProgress: function() {
			},


			onAnimationComplete: function() {
			},


			legendTemplate: '<ul class=###PROTECTED0###><% for (var i=0; i<datasets.length; i++){%><li><span style=###PROTECTED1###></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>'
		};

		var ctx = $("#chart2").get(0).getContext("2d");

		var chart2 = new Chart(ctx).Bar(data, options);

		var legend = chart2.generateLegend();

		$('#chart2Legend').append(legend);
	};
	var chart3Handler = function() {

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


		var options = {


			responsive: false,


			segmentShowStroke: true,


			segmentStrokeColor: '#fff',


			segmentStrokeWidth: 2,


			percentageInnerCutout: 50,


			animationSteps: 100,


			animationEasing: 'easeOutBounce',


			animateRotate: true,


			animateScale: false,


			legendTemplate: '<ul class=###PROTECTED9###><% for (var i=0; i<segments.length; i++){%><li><span style=###PROTECTED10###></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'

		};

		var ctx = $("#chart4").get(0).getContext("2d");

		var chart4 = new Chart(ctx).Radar(data, options);

		var legend = chart4.generateLegend();

		$('#chart4Legend').append(legend);
	};

	var sparkResize;
	$(window).resize(function(e) {
		clearTimeout(sparkResize);
		sparkResize = setTimeout(sparklineHandler, 500);
	});
	var sparklineHandler = function() {

		$(".sparkline-1 span").sparkline([300, 523, 982, 811, 1300, 1125, 1487], {
			type: "bar",
			barColor: "#D43F3A",
			barWidth: "5",
			height: "24",
			tooltipFormat: '<span style=###PROTECTED23###>&#9679;</span> {{offset:names}}: {{value}}',
			tooltipValueLookups: {
				names: {
					0: 'Sunday',
					1: 'Monday',
					2: 'Tuesday',
					3: 'Wednesday',
					4: 'Thursday',
					5: 'Friday',
					6: 'Saturday'

				}
			}
		});
		$(".sparkline-2 span").sparkline([400, 650, 886, 443, 502, 412, 353], {
			type: "bar",
			barColor: "#5CB85C",
			barWidth: "5",
			height: "24",
			tooltipFormat: '<span style=###PROTECTED29###>&#9679;</span> {{offset:names}}: {{value}}',
			tooltipValueLookups: {
				names: {
					0: 'Sunday',
					1: 'Monday',
					2: 'Tuesday',
					3: 'Wednesday',
					4: 'Thursday',
					5: 'Friday',
					6: 'Saturday'

				}
			}
		});
		$(".sparkline-3 span").sparkline([4879, 6567, 5022, 8890, 9234, 7128, 4811], {
			type: "bar",
			barColor: "#46B8DA",
			barWidth: "5",
			height: "24",
			tooltipFormat: '<span style=###PROTECTED35###>&#9679;</span> {{offset:names}}: {{value}}',
			tooltipValueLookups: {
				names: {
					0: 'Sunday',
					1: 'Monday',
					2: 'Tuesday',
					3: 'Wednesday',
					4: 'Thursday',
					5: 'Friday',
					6: 'Saturday'

				}
			}
		});
		$(".sparkline-4 span").sparkline([1122, 1735, 559, 2534, 1600, 2860, 1345, 1987, 2675, 457, 3965, 3765], {
			type: "line",
			lineColor: '#8e8e93',
			width: "80%",
			height: "47",
			fillColor: "",
			spotRadius: 4,
			lineWidth: 1,
			resize: true,
			spotColor: '#ffffff',
			minSpotColor: '#D9534F',
			maxSpotColor: '#5CB85C',
			highlightSpotColor: '#CE4641',
			highlightLineColor: '#c2c2c5',
			tooltipFormat: '<span style=###PROTECTED41###>&#9679;</span> {{offset:names}}: {{y:val}}',
			tooltipValueLookups: {
				names: {
					0: 'January',
					1: 'February',
					2: 'March',
					3: 'April',
					4: 'May',
					5: 'June',
					6: 'July',
					7: 'August',
					8: 'September',
					9: 'October',
					10: 'November',
					11: 'December'

				}
			}
		});
		$(".sparkline-5 span").sparkline([422, 1335, 1059, 2235, 1300, 1860, 1126, 1387, 1675, 1357, 2165, 1765], {
			type: "line",
			lineColor: '#8e8e93',
			width: "80%",
			height: "47",
			fillColor: "",
			spotRadius: 4,
			lineWidth: 1,
			resize: true,
			spotColor: '#ffffff',
			minSpotColor: '#D9534F',
			maxSpotColor: '#5CB85C',
			highlightSpotColor: '#CE4641',
			highlightLineColor: '#c2c2c5',
			tooltipFormat: '<span style=###PROTECTED47###>&#9679;</span> {{offset:names}}: {{y:val}}',
			tooltipValueLookups: {
				names: {
					0: 'January',
					1: 'February',
					2: 'March',
					3: 'April',
					4: 'May',
					5: 'June',
					6: 'July',
					7: 'August',
					8: 'September',
					9: 'October',
					10: 'November',
					11: 'December'

				}
			}
		});
	};
	return {
		init: function() {
			chart1Handler();
			chart2Handler();
			chart3Handler();
			chart4Handler();
			sparklineHandler();
		}
	};
}();
