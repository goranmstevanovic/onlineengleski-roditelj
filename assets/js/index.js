$(function() {
    "use strict";

	
// chart 1

  // var ctx = document.getElementById("chart1").getContext('2d');
   
  // var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
  //     gradientStroke1.addColorStop(0, '#6078ea');  
  //     gradientStroke1.addColorStop(1, '#17c5ea'); 
   
  // var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
  //     gradientStroke2.addColorStop(0, '#ff8359');
  //     gradientStroke2.addColorStop(1, '#ffdf40');

  //     var myChart = new Chart(ctx, {
  //       type: 'bar',
  //       data: {
  //         labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
  //         datasets: [{
  //           label: 'Laptops',
  //           data: [65, 59, 80, 81,65, 59, 80, 81,59, 80, 81,65],
  //           borderColor: gradientStroke1,
  //           backgroundColor: gradientStroke1,
  //           hoverBackgroundColor: gradientStroke1,
  //           pointRadius: 0,
  //           fill: false,
  //           borderRadius: 20,
  //           borderWidth: 0
  //         }, {
  //           label: 'Mobiles',
  //           data: [28, 48, 40, 19,28, 48, 40, 19,40, 19,28, 48],
  //           borderColor: gradientStroke2,
  //           backgroundColor: gradientStroke2,
  //           hoverBackgroundColor: gradientStroke2,
  //           pointRadius: 0,
  //           fill: false,
  //           borderRadius: 20,
  //           borderWidth: 0
  //         }]
  //       },
		
  //       options: {
	// 			  maintainAspectRatio: false,
  //         barPercentage: 0.5,
  //         categoryPercentage: 0.8,
	// 			  plugins: {
	// 				  legend: {
	// 					  display: false,
	// 				  }
	// 			  },
	// 			  scales: {
	// 				  y: {
	// 					  beginAtZero: true
	// 				  }
	// 			  }
	// 		  }
  //     });
// $.getJSON('api/statistika_meseci.php', function(response) {

//     const labels = [];
//     const uplate = [];

//     const meseci = [
//         'jan', 'feb', 'mar', 'apr', 'maj', 'jun',
//         'jul', 'avg', 'sep', 'okt', 'nov', 'dec'
//     ];

//     response.forEach(row => {

//         const mesecNaziv = meseci[row.mesec - 1];

//         labels.push(`${mesecNaziv} ${row.godina}`);
//         uplate.push(parseFloat(row.ukupno_uplate));
//     });

//     const ctx = document.getElementById("chart1").getContext('2d');

//     const gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
//     gradientStroke1.addColorStop(0, '#6078ea');
//     gradientStroke1.addColorStop(1, '#17c5ea');

//     new Chart(ctx, {
//         type: 'bar',
//         data: {
//             labels: labels,
//             datasets: [{
//                 label: 'Uplate (RSD)',
//                 data: uplate,
//                 borderColor: gradientStroke1,
//                 backgroundColor: gradientStroke1,
//                 borderRadius: 15,
//                 borderWidth: 0
//             }]
//         },
//         options: {
//             maintainAspectRatio: false,
//             scales: {
//                 y: {
//                     beginAtZero: true
//                 }
//             }
//         }
//     });

// });
$.getJSON('api/statistika_meseci.php', function(response) {

    const labels = [];
    const uplate = [];

    const meseciRaw = [];
    const godineRaw = [];

    const meseci = [
        'jan','feb','mar','apr','maj','jun',
        'jul','avg','sep','okt','nov','dec'
    ];

    response.forEach(row => {

        const mesecNaziv = meseci[row.mesec - 1];

        labels.push(`${mesecNaziv} ${row.godina}`);
        uplate.push(parseFloat(row.ukupno_uplate));

        meseciRaw.push(row.mesec);
        godineRaw.push(row.godina);
    });

    const ctx = document.getElementById("chart1").getContext('2d');

    const gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
    gradientStroke1.addColorStop(0, '#6078ea');
    gradientStroke1.addColorStop(1, '#17c5ea');

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Uplate (RSD)',
                data: uplate,
                borderColor: gradientStroke1,
                backgroundColor: gradientStroke1,
                borderRadius: 15,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,

            scales: {
                y: {
                    beginAtZero: true
                }
            },

            onClick: function(evt, elements) {

                if(elements.length > 0){

                    const index = elements[0].index;

                    const mesec = meseciRaw[index];
                    const godina = godineRaw[index];

                    // učitaj sadržaj
                    $("#mesecModalContent").load(
                        "mesecna_statistika.php?mesec="+mesec+"&godina="+godina
                    );

                    // otvori modal
                    const modal = new bootstrap.Modal(
                        document.getElementById('mesecModal')
                    );

                    modal.show();
                }

            }
        }
    });

});	 
// chart 2
$.getJSON('api/statistika_meseci.php', function(response) {

    const labels = [];
    const zakazani = [];
    const odrzani = [];

    const meseci = [
        'jan', 'feb', 'mar', 'apr', 'maj', 'jun',
        'jul', 'avg', 'sep', 'okt', 'nov', 'dec'
    ];

    response.forEach(row => {

        const mesecNaziv = meseci[row.mesec - 1];

        labels.push(`${mesecNaziv} ${row.godina}`);

        zakazani.push(parseInt(row.broj_zakazanih));
        odrzani.push(parseInt(row.broj_odrzanih));
    });

    const chart2Canvas = document.getElementById("chart2");
    if (!chart2Canvas) return;

    const ctx = chart2Canvas.getContext('2d');

    const gradientZakazani = ctx.createLinearGradient(0, 0, 0, 300);
    gradientZakazani.addColorStop(0, '#ff8359');
    gradientZakazani.addColorStop(1, '#ffdf40');

    const gradientOdrzani = ctx.createLinearGradient(0, 0, 0, 300);
    gradientOdrzani.addColorStop(0, '#4776e6');
    gradientOdrzani.addColorStop(1, '#8e54e9');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Zakazani',
                    data: zakazani,
                    backgroundColor: gradientZakazani,
                    borderRadius: 8
                },
                {
                    label: 'Odrzani',
                    data: odrzani,
                    backgroundColor: gradientOdrzani,
                    borderRadius: 8
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                x: {
                    stacked: false
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });

});

//  var ctx = document.getElementById("chart2").getContext('2d');

//   var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke1.addColorStop(0, '#fc4a1a');
//       gradientStroke1.addColorStop(1, '#f7b733');

//   var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke2.addColorStop(0, '#4776e6');
//       gradientStroke2.addColorStop(1, '#8e54e9');


//   var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke3.addColorStop(0, '#ee0979');
//       gradientStroke3.addColorStop(1, '#ff6a00');
	  
// 	var gradientStroke4 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke4.addColorStop(0, '#42e695');
//       gradientStroke4.addColorStop(1, '#3bb2b8');

//       var myChart = new Chart(ctx, {
//         type: 'doughnut',
//         data: {
//           labels: ["Jeans", "T-Shirts", "Shoes", "Lingerie"],
//           datasets: [{
//             backgroundColor: [
//               gradientStroke1,
//               gradientStroke2,
//               gradientStroke3,
//               gradientStroke4
//             ],
//             hoverBackgroundColor: [
//               gradientStroke1,
//               gradientStroke2,
//               gradientStroke3,
//               gradientStroke4
//             ],
//             data: [25, 80, 25, 25],
// 			borderWidth: [1, 1, 1, 1]
//           }]
//         },
//         options: {
//           maintainAspectRatio: false,
//           cutout: 82,
//           plugins: {
//             legend: {
//                 display: false,
//              }
//           }
          
//        }
//       });

   

// worl map

// jQuery('#geographic-map-2').vectorMap(
// {
//     map: 'world_mill_en',
//     backgroundColor: 'transparent',
//     borderColor: '#818181',
//     borderOpacity: 0.25,
//     borderWidth: 1,
//     zoomOnScroll: false,
//     color: '#009efb',
//     regionStyle : {
//         initial : {
//           fill : '#008cff'
//         }
//       },
//     markerStyle: {
//       initial: {
// 				r: 9,
// 				'fill': '#fff',
// 				'fill-opacity':1,
// 				'stroke': '#000',
// 				'stroke-width' : 5,
// 				'stroke-opacity': 0.4
//                 },
//                 },
//     enableZoom: true,
//     hoverColor: '#009efb',
//     markers : [{
//         latLng : [21.00, 78.00],
//         name : 'Lorem Ipsum Dollar'
      
//       }],
//     hoverOpacity: null,
//     normalizeFunction: 'linear',
//     scaleColors: ['#b6d6ff', '#005ace'],
//     selectedColor: '#c9dfaf',
//     selectedRegions: [],
//     showTooltip: true,
// });


// chart 3

//  var ctx = document.getElementById('chart3').getContext('2d');

//   var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke1.addColorStop(0, '#00b09b');
//       gradientStroke1.addColorStop(1, '#96c93d');

//       var myChart = new Chart(ctx, {
//         type: 'line',
//         data: {
//           labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
//           datasets: [{
//                 label: 'Facebook',
//                 data: [5, 30, 16, 23, 8, 14, 2],
//                 backgroundColor: [
//                   gradientStroke1
//                 ],
// 				fill: {
// 					target: 'origin',
// 					above: 'rgb(21 202 32 / 15%)',   // Area will be red above the origin
// 					//below: 'rgb(21 202 32 / 100%)'   // And blue below the origin
// 				  }, 
//                 tension: 0.4,
//                 borderColor: [
//                   gradientStroke1
//                 ],
//                 borderWidth: 3
//             }]
//         },
//         options: {
// 				  maintainAspectRatio: false,
// 				  plugins: {
// 					  legend: {
// 						  display: false,
// 					  }
// 				  },
// 				  scales: {
// 					  y: {
// 						  beginAtZero: true
// 					  }
// 				  }
// 			  }
//       });



// chart 4

// var ctx = document.getElementById("chart4").getContext('2d');

//   var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke1.addColorStop(0, '#ee0979');
//       gradientStroke1.addColorStop(1, '#ff6a00');
    
//   var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke2.addColorStop(0, '#283c86');
//       gradientStroke2.addColorStop(1, '#39bd3c');

//   var gradientStroke3 = ctx.createLinearGradient(0, 0, 0, 300);
//       gradientStroke3.addColorStop(0, '#7f00ff');
//       gradientStroke3.addColorStop(1, '#e100ff');

//       var myChart = new Chart(ctx, {
//         type: 'pie',
//         data: {
//           labels: ["Completed", "Pending", "Process"],
//           datasets: [{
//             backgroundColor: [
//               gradientStroke1,
//               gradientStroke2,
//               gradientStroke3
//             ],

//              hoverBackgroundColor: [
//               gradientStroke1,
//               gradientStroke2,
//               gradientStroke3
//             ],

//             data: [50, 50, 50],
//       borderWidth: [1, 1, 1]
//           }]
//         },
//         options: {
//           maintainAspectRatio: false,
//           cutout: 95,
//           plugins: {
//             legend: {
//                 display: false,
//              }
//           }
          
//        }
//       });

	  



  // chart 5

    // var ctx = document.getElementById("chart5").getContext('2d');
   
    //   var gradientStroke1 = ctx.createLinearGradient(0, 0, 0, 300);
    //   gradientStroke1.addColorStop(0, '#f54ea2');
    //   gradientStroke1.addColorStop(1, '#ff7676');

    //   var gradientStroke2 = ctx.createLinearGradient(0, 0, 0, 300);
    //   gradientStroke2.addColorStop(0, '#42e695');
    //   gradientStroke2.addColorStop(1, '#3bb2b8');

    //   var myChart = new Chart(ctx, {
    //     type: 'bar',
    //     data: {
    //       labels: [1, 2, 3, 4, 5],
    //       datasets: [{
    //         label: 'Clothing',
    //         data: [40, 30, 60, 35, 60],
    //         borderColor: gradientStroke1,
    //         backgroundColor: gradientStroke1,
    //         hoverBackgroundColor: gradientStroke1,
    //         pointRadius: 0,
    //         fill: false,
    //         borderWidth: 1
    //       }, {
    //         label: 'Electronic',
    //         data: [50, 60, 40, 70, 35],
    //         borderColor: gradientStroke2,
    //         backgroundColor: gradientStroke2,
    //         hoverBackgroundColor: gradientStroke2,
    //         pointRadius: 0,
    //         fill: false,
    //         borderWidth: 1
    //       }]
    //     },
    //     options: {
		// 		  maintainAspectRatio: false,
    //       barPercentage: 0.5,
    //       categoryPercentage: 0.8,
		// 		  plugins: {
		// 			  legend: {
		// 				  display: false,
		// 			  }
		// 		  },
		// 		  scales: {
		// 			  y: {
		// 				  beginAtZero: true
		// 			  }
		// 		  }
		// 	  }
    //   });




   });	 
   