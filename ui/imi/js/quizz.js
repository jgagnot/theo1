// la méthode quizzContructor crée un quizz dans l'élément html data.target
// il suffit de créer un conteneur (exemple : <div id="myQuizz"></div>) et d'appeler cette méthode en passant comme argument un json connstitué des questions et réponses du quizz
// après chaque réponse, la méthode data.callback (si elle est settée dans le json data) est appelée et retourne un json de valeurs

/* le json data de QuizzContructor est ainsi constitué :

    quizzContructor({
        'target':'myQuizz',
        'question': {
            0: {
                'title': 'Combien de fois la France a-t-elle remporté la coupe du monde de football ?',
                'choice': {
                    0: 'Une fois',
                    1: 'Deux fois',
                    2: 'Trois fois'
                },
                'correct': 2
            },
            1: {
                'title': 'Qui a marqué le dernier but en finale de la coupe du monde 2018 ?',
                'choice': {
                    0: 'Kylian Mbappé',
                    1: 'Paul Pogba',
                    2: 'Mario Mandžukić'
                },
                'correct': 3
            },
            2: {
                'title': 'Outre l’Argentine et la France, quel pays a remporté deux fois la coupe du monde de football ?',
                'choice': {
                    0: 'Angleterre',
                    1: 'Uruguay',
                    2: 'Italie'
                },
                'correct': 2
            },
        },
        'titleContainer': {
            'tag':'h5',                         //type de conteneur du titre de la question (falcultatif : "h4" par défaut)
            'class':'mt-3'                      //type de conteneur du titre de la question (falcultatif : "mt-3" par défaut)
        },
        'carousel':0,                           //booléen (0 par défaut) : 1 quand chaque question est un item de carousel (la classe .active est settée sur le premier slide)
        'faSize':'',                            //taille du picto fontawesome (facultatif)
        'faEmpty':'fal fa-circle',              //type de picto fontawesome du bouton radio vide (facultatif : "fal fa-circle" par défaut)
        'faCorrect':'fas fa-check-circle',      //type de picto fontawesome du bouton radio correct (facultatif : "fas fa-check-circle" par défaut)
        'faIncorrect':'fas fa-times-circle',    //type de picto fontawesome du bouton radio correct (facultatif : "fas fa-times-circle" par défaut)
        'colorCorrect':'text-success',          //classe du bouton radio correct (facultatif : "text-success" par défaut)
        'colorIncorrect':'text-danger',         //classe du bouton radio correct (facultatif : "text-danger" par défaut)
        'callback':quizzCallback                //function callback appelée après chaque réponse (facultatif)
    });

*/

/* le json retourné par data.callback est ainsi constitué :
    {
        'target': id de la l'élément html dans laquelle est injecté le quizz
        'questionNumber': nombre de questions dans le quizz
        'lastAnswered': dernière question répondue,
        'lastSelected': dernière réponse choisie,
        'lastCorrect': bonne réponse à la dernière question répondue,
        'questionNumberAnswered': nombre de questions répondues,
        'questionNumberCorrect': nombre de bonnes réponses
    }
*/

function quizzContructor (data) {

    var titleTag=typeof data.titleContainer.tag !== "undefined"?data.titleContainer.tag:'h4';
    var titleClass=typeof data.titleContainer.class !== "undefined"?data.titleContainer.class:'mt-3';
    var carousel=typeof data.carousel !== "undefined"?data.carousel:0;
    var faEmpty=typeof data.faEmpty !== "undefined"?data.faEmpty:'fal fa-circle';
    var faCorrect=typeof data.faCorrect !== "undefined"?data.faCorrect:'fas fa-check-circle';
    var faIncorrect=typeof data.faIncorrect !== "undefined"?data.faIncorrect:'fas fa-times-circle';
    var colorCorrect=typeof data.colorCorrect !== "undefined"?data.colorCorrect:'text-success';
    var colorIncorrect=typeof data.colorIncorrect !== "undefined"?data.colorIncorrect:'text-danger';

    var html='';
    $.each(data.question, function( index1, value1 ) {

        // <carousel>
        if (carousel) html+= "<div class='carousel-item"+(parseInt(index1)===0?" active":"")+"'>";

        html+="<"+titleTag+" class='"+titleClass+"'>"+value1.title+"</"+titleTag+">";

        html+="<div id='"+data.target+"_"+index1+"' class='col-6 radio-container quizz-question'>";

        $.each(value1.choice, function( index2, value2 ) {

            html+="<div id='"+data.target+"Choice_"+index1+"_"+index2+"_"+value1.correct+"' class='fa-radio-value--"+index2+"'>";

            html+="<i class='";
            html+=(parseInt(index2)===parseInt(value1.correct)?faCorrect+" "+colorCorrect:faIncorrect+" "+colorIncorrect);
            html+=" fa-lg fa-radio fa-radio-on d-none'></i>";


            html+="<i class='"+faEmpty+" fa-lg text-gray-500 fa-radio fa-radio-off'></i>";
            html+="<label class='fa-radio'>"+value2+"</label>";
            html+='</div>';
        });
        html+='</div>';

        // <!carousel>
        if (carousel) html+= "</div>";

    });

    $('#'+data.target).html(html); //on injecte le quizz
    faRadioClick();

    if (typeof(data.callback) === "function") {
        $('#'+data.target+' .fa-radio').click(function () {
            data.callback({
                'target':data.target,
                'questionNumber':Object.keys(data.question).length,
                'lastAnswered':$(this).parent().attr('id').split('_')[1],
                'lastSelected':$(this).parent().attr('id').split('_')[2],
                'lastCorrect':$(this).parent().attr('id').split('_')[3],
                'questionNumberAnswered':Object($('#'+data.target+' .quizz-question .fa-radio-on:not(.d-none)')).length,
                'questionNumberCorrect':Object($('#'+data.target+' .quizz-question .'+faCorrect.split(' ')[1]+'.'+colorCorrect+':not(.d-none)')).length,
            })
        });
    }

}