const canvas = document.getElementById("graficoOcupacion");

if (canvas) {

    new Chart(canvas, {

        type: "doughnut",

        data: {

            labels: [

                "Disponibles",

                "Ocupadas"

            ],

            datasets: [{

                data: [6,18]

            }]

        }

    });

}