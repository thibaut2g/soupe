document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.datepicker');
    var options = {
        format: 'dddd dd mmmm yyyy',
        firstDay: 1,
        i18n: {
            cancel: 'Annuler',
            clear: 'Effacer',
            months:[
                'Janvier',
                'Février',
                'Mars',
                'Avril',
                'Mai',
                'Juin',
                'Juillet',
                'Août',
                'Septembre',
                'Octobre',
                'Novembre',
                'Decembre'
            ],
            monthsShort:[
                'Jan',
                'Fev',
                'Mar',
                'Apr',
                'Mai',
                'Juin',
                'Juil',
                'Aou',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ],
            weekdays:[
                'Dimanche',
                'Lundi',
                'Mardi',
                'Mercredi',
                'Jeudi',
                'Vendredi',
                'Samedi'
            ],
            weekdaysShort:[
                'Dim',
                'Lun',
                'Mar',
                'Mer',
                'Jeu',
                'Ven',
                'Sam'
            ],
            weekdaysAbbrev:	['D','L','M','M','J','V','S']
        }

    };
    var instances = M.Datepicker.init(elems, options);
});


document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('.collapsible');
    var instances = M.Collapsible.init(elems, {});
});

var userInfos = document.getElementsByClassName("userinfo");

for (let user of userInfos) {
    user.addEventListener('click', function(){user.nextSibling.classList.toggle("hiddendiv");
    });
}

(function() {
    var httpRequest;
    document.getElementById("datepicker_form").addEventListener('submit', makeRequest);

    function makeRequest() {
        var date = document.getElementById("datepicker").value;
        httpRequest = new XMLHttpRequest();

        if (!httpRequest) {
            alert('Abandon. Impossible de créer une instance de XMLHTTP');
            return false;
        }
        httpRequest.onreadystatechange = alertContents;
        httpRequest.open('GET', '/saveDate/' + date);
        httpRequest.send();
    }

    function alertContents() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status !== 200) {
                alert('Merci de vérifier que votre inscription a bien été prise en compte.');
            } else {
                if(httpRequest.responseText === "ok") {
                    alert("Inscription validée");
                } else {
                    alert("Nombre maximum de participants atteint ou veuillez contacter l'administrateur du site.");
                }
            }
        }
    }
})();

(function() {
    var httpRequest;

    var subscribes = document.getElementsByClassName("subscribe");

    for (let item of subscribes) {
        item.addEventListener('click', function(){makeRequest(item.dataset.date)});
    }


    function makeRequest(date) {
        var askedDate = date.split("-");
        if (confirm("Confirmer votre inscription pour le " + askedDate[2] + "/" + askedDate[1] + "/" + askedDate[0])) {
            httpRequest = new XMLHttpRequest();

            if (!httpRequest) {
                alert('Abandon. Impossible de créer une instance de XMLHTTP');
                return false;
            }
            httpRequest.onreadystatechange = alertContents;
            httpRequest.open('GET', '/saveDate/' + date);
            httpRequest.send();
        }
    }

    function alertContents() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status !== 200) {
                alert('Merci de vérifier que votre inscription a bien été prise en compte.');
            } else {
                if(httpRequest.responseText === "ok") {
                    location.reload();
                } else {
                    alert("Nombre maximum de participants atteint ou veuillez contacter l'administrateur du site. thibaut.de-gouberville@2018.icam.fr.");
                }
            }
        }
    }
})();


(function() {
    var httpRequest;

    var subscribes = document.getElementsByClassName("unsubscribe");

    for (let item of subscribes) {
        item.addEventListener('click', function(){makeRequest(item.dataset.date)});
    }


    function makeRequest(date) {
        var askedDate = date.split("-");
        console.log(askedDate);
        if (confirm("Se désinscrire de cette date : " + askedDate[2] + "/" + askedDate[1] + "/" + askedDate[0] + " ?")) {
            httpRequest = new XMLHttpRequest();

            if (!httpRequest) {
                alert('Abandon. Impossible de créer une instance de XMLHTTP');
                return false;
            }
            httpRequest.onreadystatechange = alertContents;
            httpRequest.open('GET', '/unsubscribeDate/' + date);
            httpRequest.send();
        }
    }

    function alertContents() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status !== 200) {
                alert('Erreur inconnue');
            } else {
                if(httpRequest.responseText === "ok") {
                    location.reload();
                } else {
                    alert("Erreur inconnue.");
                }
            }
        }
    }
})();

