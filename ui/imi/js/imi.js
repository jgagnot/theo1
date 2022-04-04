/**
 * Created by nicolasdelourme on 10/12/2017.
 */

function f3Path() {
    return $('#f3Path').val();
}

function f3ImagePath() {
    return $('#f3ImagePath').val();
}

function getRandomInteger(max,min=0) {
    //cette méthode retourne un entier entre 0 et max
    return Math.floor(min + Math.random()*(max + 1 - min))
}

function capitalizeFirstLetter(string) {
    // cette méthode retourne string avec la poremière lettre en capitale
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function fullMaskShow (z=10,oapcity=0.8) {
    //cette méthode crée un mask plein écran à la valeur z-index
    $('body').append('<div class="full-mask" style="z-index:'+z+';opacity:'+oapcity+';display:none"></div>');
    $('.full-mask').fadeIn(400);
}

function fullMaskDestroy () {
    //cette méthode crée un mask plein écran à la valeur z-index
    $('.full-mask').fadeOut(400,function () {
        $(this).remove();
    });
}

$.fn.cleanForm = function() {
    //cette méthode vide la valeur des input du form porteurs de la class .clean-form
    $('#'+$(this).attr('id')+' .clean-form').each(function() {
        $(this).val('');
    });
};


/************* FA-CHECKBOX ************/
/*
    Grâce à ce code, on transforme tout élément fontawesome (voir https://fontawesome.com) en case à cocher
    Le code html à coller est celui-ci :

        <div id="faCheckbox1Container" class="checkbox-container">
            <input type="checkbox" class="fa-checkbox-input d-none" id="monInputId" name="monInputName">
            <i class="fas fa-check-square fa-2x text-danger checkbox-fa checkbox-fa-on d-none"></i>
            <i class="fal fa-square fa-2x text-project-third-light checkbox-fa checkbox-fa-off"></i>
            <label class="checkbox-fa">Cochez-moi !</label>
        </div>

    Il suffit de personnaliser une seule valeur : l'id du conteneur (ici "faCheckbox1Container").

    L'état de la case à cocher se récuoère ensuite en JS par : $('#'+idContainer+' .checkbox-fa-input').is(':checked');

    - Dans un formulaire, on peut aussi ajouter une balise unique name à <input> puis récupérer la valeur via une soumission en Get ou en Post
    - En ajoutant un Id à l'input comme "monInputId", on peut aussi lui créer un label : for="monInputId"

    On peut librement changer via les valises <i> de Fontawesome le picto, sa couleur, sa taille.

*/

$(document).ready(function () {
    faCheckboxClick();
});

function faCheckboxClick() {
    $(document).on('click', '.checkbox-container:not(.disabled) .checkbox-fa', function (e) {
        var Container = $(this).closest('.checkbox-container');

        Container.find('.checkbox-fa-input').trigger("change");
    });

    $(document).on('change', '.checkbox-fa-input', function (e) {
        var Container = $(this).closest('.checkbox-container');

        if (Container.find('.checkbox-fa-input').prop('checked') === true) {

            Container.find('.checkbox-fa-input').prop('checked', false);
            Container.find('.checkbox-fa-off').removeClass('d-none');
            Container.find('.checkbox-fa-on').addClass('d-none');
        } else {

            Container.find('.checkbox-fa-input').prop('checked', true);
            Container.find('.checkbox-fa-on').removeClass('d-none');
            Container.find('.checkbox-fa-off').addClass('d-none');
        }

    });
}
/************ /FA-CHECKBOX ************/


/************** FA-RADIO **************/

/*
    Grâce à ce code, on transforme tout élément fontawesome (voir https://fontawesome.com) en bouton radio
    Le code html à coller est celui-ci :

    <div id="faRadio1Container" class="radio-container">
        <input class="radio-fa-input-value" type="hidden" value="1">
        <span class="radio-fa-input-value--1">
            <i class="fas fa-check-circle fa-2x text-danger radio-fa-input radio-fa-input-on"></i>
            <i class="fal fa-circle fa-2x text-project-third-light radio-fa-input radio-fa-input-off d-none"></i>
            <label class="radio-fa-input">Choix 1</label>
        </span>
        <span class="radio-fa-input-value--2">
            <i class="fas fa-check-circle fa-2x text-danger radio-fa-input radio-fa-input-on d-none"></i>
            <i class="fal fa-circle fa-2x text-project-third-light radio-fa-input radio-fa-input-off"></i>
            <label class="radio-fa-input">Choix 2</label>
        </span>
        <span class="radio-fa-input-value--3">
            <i class="fas fa-check-circle fa-2x text-danger radio-fa-input radio-fa-input-on d-none"></i>
            <i class="fal fa-circle fa-2x text-project-third-light radio-fa-input radio-fa-input-off"></i>
            <label class="radio-fa-input">Choix 3</label>
        </span>
    </div>

    Il suffit de personnaliser une seule valeur : l'id du conteneur (ici "faCheckbox1Container").

    L'id du radio actif se récupère ensuite en JS par : $('#' + idContainer + ' .fa-radio-value').val();

    - Dans un formulaire, on peut aussi ajouter une balise unique name à <input> puis récupérer la valeur via une soumission en Get ou en Post
    - En ajoutant un Id à l'input comme "monInputId", on peut aussi lui créer un label : for="monInputId"

    On peut librement changer via les valises <i> de Fontawesome le picto, sa couleur, sa taille.

*/


$(document).ready(function () {
    faRadioClick();
});
function faRadioClick () {
    $(document).on('click', '.radio-container:not(.disabled) .radio-fa-input,.radio-container:not(.disabled) .radio-label', function (e) {
        e.stopImmediatePropagation();
        var container = $(this).closest('div.radio-container');
        var radioNumber = $(this).parent('div').attr('class');

        container.find('.radio-fa-input-on').addClass('d-none');
        container.find('.radio-fa-input-off').removeClass("d-none");
        container.find(' .' + radioNumber + ' .radio-fa-input-off').addClass('d-none');
        container.find(' .' + radioNumber + ' .radio-fa-input-on').removeClass('d-none');

        container.find(' .radio-fa-input-value').val(radioNumber.replace('radio-fa-input-value--', ''));

    });
}
/************* /FA-RADIO **************/


/*********** SCROLL-TARGET ************/

// cette méthode permet de scroller verticalement au click d'un élément porteur de la class .scroll-target vers l'objet indiqué dans l'attribut 'data-target'
// par exemple : <button class="scroll-target" data-target="#myDiv"> permet de scroller jusqu'à ce que le haut de #my-div atteigne le haut de la fenêtre
$(document).ready(function () {
    $('.scroll-target').each(function () {
        $(this).click(function (event) {
            var target = $(this).attr('data-target');
            var position = $(target).offset().top;
            $("body, html").animate({
                scrollTop: position - 158,
                easing: 'swing'
            }, 300);
        });
    });
});

/*********** /SCROLL-TARGET ***********/


/************** IN-VIEW ***************/

$.fn.inView = function(callback,once=1){
    // cette méthode appelle la méthode [callback] lorsque l'objet instancié est entièrement visible dans la fenêtre du navigateur
    // pour passer des arguments à la méthode callback, il convient de passer les arguments ainsi : $('#myDiv').inView([callbackMethod].bind(null,[arg1],[arg1],[arg3],…)
    // si once===1, la méthode [callback] est appelée une seule fois
    var scrollPosition = $(window).scrollTop();
    var visibleArea = $(window).scrollTop() + $(window).height();
    var objEndPos = ($(this).offset().top + $(this).height());

    if (visibleArea >= objEndPos && scrollPosition <= objEndPos && $(this).hasClass('inViewLaunched')===false) {
        callback();
        if (once) $(this).addClass('inViewLaunched');
    }
};



/************ ANIMATE CSS *************/

// Voir les animations : https://daneden.github.io/animate.css/

function animate(container, effect, callback, classAfter='') {
    //cette méthode permet d'appeler une animation dans /ui/css/animate
    //lorsqu'on veut RETIRER le container de la page en attendant le début de l'animation, il faut lui ajouter la class : "hide-before-animation"
    //lorsqu'on veut JUSTE OPACIFIER A 0 (cacher) le container de la page en attendant le début de l'animation, il faut lui ajouter la class : "opacify-before-animation"

    //exemple : animate('#maDiv','bounce');

    //aux animations de daneden, on ajoute deux animations fadeIn_XXX et fadeOut_XXX ou XXX indique le delay en ms pour le fadeIn

    if (effect.indexOf('fadeIn_')===-1 && effect.indexOf('fadeOut_')===-1) {
        $(container).removeAttr('class').addClass(effect + ' ' + classAfter+' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
            if (typeof(callback) === "function") callback($(this),effect);
        });
    } else {
        if (effect.indexOf('fadeIn_')!==-1) $(container).fadeTo(0,0).removeClass('opacify-before-animation '+classAfter.split('_')[0]).fadeTo(parseInt(effect.split('_')[1]),1).addClass(classAfter);
        if (effect.indexOf('fadeOut_')!==-1) $(container).removeClass(classAfter.split('_')[0]).fadeTo(parseInt(effect.split('_')[1]),0).addClass(classAfter);
        if (typeof(callback) === "function") callback($(this),effect);
    }
}

/* loop animate sur une div unique */

function loopAnimate(object,effect,delay) {
    //cette méthode lance l'animation [effect] de manière infinie toutes les [delay] millisecondes
    //elle s'arrête définitivement si l'objet est porteur de la class "stop-animate"
    if (typeof delay !== 'undefined') {
        object.attr('data-delay',delay);
    } else {
        delay=parseInt(object.attr('data-delay'));
    }
    object.removeAttr('class');
    setTimeout(function () {
        if (!$(object).hasClass('stop-animate')) animate('#'+object.attr('id'),effect,loopAnimate);
    },delay);
}

/* loop animate sur un groupe de div */

function loopAnimateOneByOne(group,effect="bounce",order=1) {
    // cette méthode lance l'animation [effect] une fois sur CHACUNE des div porteuses de la class [group] à tour de rôle (une fois que l'animation de la div précédente esy terminée)
    // les div sont animés dans l'ordre du DOM si order==1 (ou l'ordre inverse si ==0)
    $('.'+group+(order===1?':first':':last')).attr('id',group).attr('data-order',order);
    animate('#'+group,effect,loopAnimateOneByOneCallback);
}

function loopAnimateOneByOneCallback(object,effect) {
    var group=$(object).attr('id');
    $(object).removeAttr('id');
    loopAnimateOneByOne(group, effect,$(object).attr('data-order'))
}


function loopAnimateAll(group,effect="bounce",order=1,delay=200,callback=null) {
    // cette méthode lance l'animation [effect] une fois sur CHACUNE des div porteuses de la class [group] toutes les [delay] millisecondes (même si l'animation de la div précédente n'est pas terminée)
    // les div sont animés dans l'ordre du DOM si order==1 (ou l'ordre inverse si ==0)
    // la class group est remplacée par la chaine concatténée [group]+"_animated"
    $('.'+group+(order===1?':first':':last')).attr('id',group);
    animate('#'+group,effect,null,group+"_animated" );

    setTimeout(function(){
        $('#'+group).removeAttr('id');
        if (($("."+group).length)>0) {
            if (!($("."+group+'.stop-animate').length)>0) loopAnimateAll(group,effect,order,delay,callback);
        } else {
            if (typeof(callback) === "function") callback(group,effect);
        }

    }, delay);

}

/************ /ANIMATE CSS ************/


/********** ADD/REMOVE CLASS **********/

$.fn.animateAddRemoveClass = function(classToAdd,classToRemove,speed=2000,duration_array=[],repeat=true) {

    // cette méthode alerne X fois l'ajout de la (ou des) [classToAdd] et la suppression de la (ou des) [classToRemove] sur l'objet : le nombre d'ajout/suppression dépend du contenu de duration_array
    // la durée des rermplacements est contenu dans [duration_array] :    - [N][1] millisecondes est la durée le remlacement est effectif (c'est-à-dire ENTRE la fin de l'ajout de [classToAdd] et le début du retrait de [classToAdd])
    //                                                                    - [N][0] millisecondes est la durée où l'opacité est à 0 (c'est-à-dire APRES le retrait de [classToAdd])
    // la vitesse de transition de class à ajouter et/ou à supprimer est de [speed] millisecondes

    // nota : - si duration_array ne contient qu'une seule valeur, elle est appliquée à tous les ajouts/suppressions
    //        - si duration_array contient [number] valeurs, la N-ième correspond à la N-ième ajouts/suppressions
    //        - [classToAdd] OU [classToRemove] peut être vide (l'un ou l'autre : OU est ici exclusif)
    // important : à l'origine, ajouter la class "opacify-before-animation" à l'objet


    /*
    L'EXEMPLE suivant génèrera une séquence de 4 d'ajout/suppression de class dont le temps dans [classToRemove] passe de 0.2s à 0.8s et la durée avec la class [classToAdd] alterne entre 1s et 2s

        var duration_array=[];

        duration_array[0] = [];
        duration_array[0][1] = 1000; // 1s avec la class [classToAdd]…
        duration_array[0][0] = 200;  // … puis 0,2s avec la class [classToRemove]

        duration_array[1] = [];
        duration_array[1][1] = 2000;
        duration_array[1][0] = 400;

        duration_array[2] = [];
        duration_array[2][1] = 1000;
        duration_array[2][0] = 600;

        duration_array[3] = [];
        duration_array[3][1] = 2000;
        duration_array[3][0] = 800;

        myObject.animateFade(1000,duration_array,true)
    */

    if (duration_array.length===0) { //si l'array duration_array est vide, on lui attribue des valeurs par défaut
        duration_array[i] = [500, 500];
    }

    $(this).css("transition", "all "+speed+"ms ease-in-out").removeClass('opacify-before-animation');

    animateAddRemoveClassExecute($(this),classToAdd,classToRemove,duration_array,speed,repeat);
};


function animateAddRemoveClassExecute(object,classToAdd,classToRemove,duration_array,speed=2000,repeat=true,round=0) {

    if (round>duration_array.length-1 && repeat) {
        animateAddRemoveClassExecute(object,classToAdd,classToRemove,duration_array,speed,repeat,0);
    } else {
        object.removeClass(classToRemove).addClass(classToAdd);
        waitingDuration1(object,classToAdd,classToRemove,duration_array,speed,repeat,round)
    }

}

function waitingDuration1 (object,classToAdd,classToRemove,duration_array,speed,repeat,round) {
    setTimeout(removeClass,speed+duration_array[round][1],object,classToAdd,classToRemove,duration_array,speed,repeat,round);
}

function removeClass (object,classToAdd,classToRemove,duration_array,speed,repeat,round) {
    object.removeClass(classToAdd).addClass(classToRemove);
    waitingDuration0(object,classToAdd,classToRemove,duration_array,speed,repeat,round);
}

function waitingDuration0 (object,classToAdd,classToRemove,duration_array,speed,repeat,round) {
    round++;
    if (!object.hasClass('stop-animate')) setTimeout(animateAddRemoveClassExecute,speed+duration_array[round-1][0],object,classToAdd,classToRemove,duration_array,speed,repeat,round);
}

/********* /ADD/REMOVE CLASS **********/


/*********** ANIMATE ROTATE ***********/

//cette méthode effectue une rotation de l'objet de [angle] degrés et pendant [duration] millisecondes.
//la methode callback est appelée à la fin de l'animation

$.fn.animateRotate = function(angle, duration, easing, callback) {
    var args = $.speed(duration, easing, callback);
    var step = args.step;
    return this.each(function(i, e) {
        args.complete = $.proxy(args.complete, e);
        args.step = function(now) {
            $.style(e, 'transform', 'rotate(' + now + 'deg)');
            if (step) return step.apply(e, arguments);
        };

        $({deg: 0}).animate({deg: angle}, args);
    });
};

/********** /ANIMATE ROTATE ***********/



/********** PROGRESS BUTTON ***********/

//les méthodes suivantes gèrent les progress-button. Une fois le bouton rempli, la méthode précisée en attribut "data-callback" dans <button> est appelée.
//ATTENTION : renseigner l'attribut 'data-callback'
$(document).ready(function () {

    var detectTap = false;

    $('.button-progress').on('touchstart mousedown', function() {
        detectTap = true;
        var object = $(this);
        var callback = $(this).attr('data-callback');
        setTimeout(function(){
            buttonProgressFull (object,callback);
        }, 700);
    });

    $('.button-progress').on('touchend mouseup', function(event) {
        detectTap = false;
    });

    function buttonProgressFull (object,callback) {
        if (detectTap===true) alert(object.attr('id')+'/'+callback);
    }

});

/********** /PROGRESS BUTTON **********/


/************** CAROUSEL **************/

$.fn.carouselItemMaxHeight = function() {
    var maxHeight=0;
    $( this ).add(' .carousel-inner .carousel-item').each(function( index ) {
        if ($(this).height()>maxHeight) maxHeight=$(this).height();
    });
    return maxHeight;
};

/************* /CAROUSEL **************/



/*************** TOAST ****************/

// cette méthode permet d'afficher un toast sur la page

// en PHP, il suffit de setter une chaine JSON (la méthode toast sera alors appelée depuis le footer) ;
// Exemple :
//      $f3->set('SESSION.toast_json','{"content":"Le texte à afficher.","hideDelay":10000,"toastBg":"success","toastFa":"fas fa-thumbs-up fa-3x"}');

// cette méthode peut aussi être appelée directement en JS via toast() ci-dessous

function htmlDecode(html) {
    var a = document.createElement('a');
    a.innerHTML = html;
    return a.textContent;
}

function toast(toast_json) {

    // cette méthode affiche automatiquement un toast-message sur la page
    // elle retourne l'id de l'objet html du toast

    // EN ARGUMENT, PASSER LE JSON [toast_json] :

    // !!! OBLIGATOIRE !!! : "content" est le contenu texte ou html du toast

    // "position" prend la valeur "top-right", "top-left", "bottom-right", "bottom-left", "top-center", "bottom-center", "middle" (par défaut "top-right" est setté)
    // "showDelay" indique le temps en ms avant que le toast ne s'affiche (par defaut : 0)
    // "hideDelay" indique le temps en ms avant que le toast ne s'efface (par defaut : 6000 soit 6s). Prend la valeur -1 si lee toast ne doit pas s'effacer
    // "showAnimate" prend le nom de l'animation qui affiche le toast (par defaut fadeIn) : voir le choix sur https://daneden.github.io/animate.css/
    // "hideAnimate" prend le nom de l'animation qui efface le toast (par defaut fadeOut) : voir le choix sur https://daneden.github.io/animate.css/
    // "toastBg" prend le nom d'une couleur du project : "success", "danger", "project-secondary"… ( (par défaut "project-secondary" est setté)
    // "toastHide" prend la valeur null si on ne veut pas qu'il soit affiché, ou html d'un fontawesome (par defaut "<i class="fas fa-times-circle"></i>")
    // "toastFa" prend la valeur null si on ne veut pas qu'il soit affiché, ou html d'un fontawesome (par defaut "<i class="fas fa-exclamation-circle fa-3x"></i>")
    // "toastContentHtml" contient la structure du code html du toast (si "toastContentHtml" est passé dans le json, il doit a minima contenir un élément porteur de la class ".toast-html"). Par défaut, le toast est composé d'un simple span sauf si un fontawesome est prévu (dans ce cas, deux colonnes dans .row : .col-2 qui reçoit le fontawesome et .col-10 qui reçoit le message pouvant lui-même être enrichi en html via l'attribut )

    // on calcule l'id du nouveau toast et on instancie le html
    var nextToastId = $('.toast-container').length + 1;
    var toastContentHtml = '';
    if (!toast_json.hasOwnProperty('toastContentHtml')) {
        if (toast_json['toastFa'] === null) {
            toastContentHtml = '<div class="toast-html"></div>';
        } else {
            toastContentHtml = '<div class="row no-gutters"><div class="col-2 toast-fa"></div><div class="col-10 toast-html"></div></div>';
        }

    } else {
        toastContentHtml = toast_json['toastContentHtml'];
    }

    var toastHtml = '<div id="toastContainer_' + nextToastId + '" class="toast-container"><div id="toastAnimate_' + nextToastId + '" class="toast-animate hide-before-animation"><div class="toast-content">' + toastContentHtml + '<div class="toast-hide"></div></div></div></div>';

    //si un toast est déjà affiché au même endroit, on le détruit
    $('.toast-container.toast-container-' + (toast_json.hasOwnProperty('position') ? toast_json['position'] : 'top-right')).fadeOut();

    $('body').append(toastHtml).ready(function () {
        toastShow(nextToastId, toast_json);
    });

    return ('toastContainer_' + nextToastId);

}

function toastShow(toastId,toast_json) {

    $('#toastContainer_'+toastId + ' .toast-content .toast-html').html(toast_json['content']);

    if (toast_json.hasOwnProperty('toastHide') && toast_json['toastHide']===null) {
        $('#toastContainer_'+toastId +' .toast-content .toast-hide').addClass('d-none');
    } else {
        if (toast_json.hasOwnProperty('toastHide')) {
            $('#toastContainer_' +toastId +' .toast-content .toast-hide').html(toast_json['toastHide']);
        } else {
            $('#toastContainer_'+toastId +' .toast-content .toast-hide').html('<i class="fas fa-times-circle"></i>');
        }
        $('#toastContainer_' +toastId +' .toast-content .toast-hide').click(function () {
            toastHide('toastContainer_'+toastId);
        });
    }

    if (toast_json.hasOwnProperty('toastFa') && toast_json['toastFa']===null) {
        $('#toastContainer_'+toastId +' .toast-content .toast-fa').addClass('d-none');
    } else {
        if (toast_json.hasOwnProperty('toastFa')) {
            $('#toastContainer_'+toastId +' .toast-content .toast-fa').html(toast_json['toastFa']);
        } else {
            $('#toastContainer_'+toastId +' .toast-content .toast-fa').html('<i class="fas fa-exclamation-circle fa-3x"></i>');
        }
    }

    $('#toastContainer_'+toastId +'.toast-container').addClass('toast-container-'+(toast_json.hasOwnProperty('position')?toast_json['position']:'top-right'));

    $('#toastContainer_'+toastId +' .toast-content').addClass(toast_json.hasOwnProperty('toastBg')?'bg-'+toast_json['toastBg']:'');

    if (toast_json.hasOwnProperty('hideAnimate')) {
        $('#toastContainer_'+toastId).attr('data-toast-hide',toast_json['hideAnimate']);
    } else {
        $('#toastContainer_'+toastId).attr('data-toast-hide','fadeOut');
    }

    setTimeout(function () {
        animate('#toastAnimate_'+toastId,(toast_json.hasOwnProperty('showAnimate')?toast_json['showAnimate']:'fadeIn'));
        if (parseInt(toast_json['fullMask'])===1) fullMaskShow(1071);
        if (toast_json['hideDelay']!==-1) {
            setTimeout(function () {
                toastHide('toastContainer_'+toastId);
                fullMaskDestroy();
            },(toast_json.hasOwnProperty('hideDelay')?toast_json['hideDelay']:6000));
        }
    },(toast_json.hasOwnProperty('showDelay')?toast_json['showDelay']:0));

    return ('toastContainer_'+toastId);

}

function toastHide (toastContainerId) {
    animate('#toastAnimate_'+toastContainerId.split('_')[1],$('#'+toastContainerId).attr('data-toast-hide'),toastHideCallback);
    fullMaskDestroy();
    //on vide le toast_json éventuel en session
    $.ajax({
        url: f3Path()+'emptyToast',
        type: 'GET',
        dataType: 'html',
        success: function (code_html, statut) {
            return code_html;
        }
    });
}

function toastHideCallback (toast) {
    //on détruit le toast
    toast.remove();
}

/*************** /TOAST ***************/



/**************** SWIPE ***************/

$.fn.swipe = function(callback,direction=null) {
    //cette péthode permet d'utiliser le Swipe issus de hammer.js (ajouter : <script type="text/javascript" src="/ui/imi/js/hammer.min.js"></script>)
    //pour "swiper" un élément, appeler [objet].swipe([callback],[direction]) : callback est obligatoire
    //par défaut les swipe sont droite et gauche seulement. direction peut prendre les valeurs all (déclenchement quelque soit la direction) ou vertical (déclenchement uniquement si le swipe est vers le haut ou bas)
    //faire un console.log de swipe dans le callback pour découvrir toutes les données exploitables

    var hammertime = new Hammer(document.getElementById($(this).attr('id')));

    if (direction==="vertical") hammertime.get('swipe').set({ direction: Hammer.DIRECTION_VERTICAL });
    if (direction==="all") hammertime.get('swipe').set({ direction: Hammer.DIRECTION_ALL });
    hammertime.on('swipe', function(swipe) {
        callback(swipe);
    });


};

/*************** /SWIPE ***************/

function bootstrapGridSize (width=$(window).width()) {
    // cette méthode retourne une string xs, sm, md, lg ou xl de la largeur passée en argument (ou de la largeur de la fenêtre courante si aucun argument n'est passé)
    var bootstrapSize='xs';
    if (width>=576) bootstrapSize='sm';
    if (width>=768) bootstrapSize='md';
    if (width>=992) bootstrapSize='lg';
    if (width>=1200) bootstrapSize='xl';
    return bootstrapSize;
}

/*** REQUIRED INPUT (VALIDATE FORM) ***/

$.fn.requiredInput = function (id='') {
    // cette méthode parcourt le form et renvoie un array des champs comportants une erreur (ou null s'il n'y a pas d'erreur)
    // pour l'utiliser, il suffit d'ajouter l'attribut data-required-regex (regex) et - en option - data-required-message (html) aux input .form-control
    // pour ne vérifier qu'un seul input du formulaire, passer son id dans [id]

    // Exemple :
    //      $('#monForm').on('submit', function (e) {
    //             e.preventDefault();
    //             var form = $(this);
    //             var requiredInput_array=form.requiredInput();
    //             …
    //      }

    var required_array=[];
    if (id!=='') id='#'+id;
    var index=0;
    $(this).find(id+'.form-control').each(function( ) {
        if (typeof $(this).attr('data-required-regex') !== typeof undefined && $(this).attr('data-required-regex') !== false && $(this).val().match($(this).attr('data-required-regex'))===null) {
            required_array[index]=[];
            required_array[index]['inputId']=$(this).attr('id');
            if (typeof $(this).attr('data-required-message') !== typeof undefined && $(this).attr('data-required-message') !== false) {
                required_array[index]['message']=$(this).attr('data-required-message');
            }
            index++;
        }
    });
    return (required_array.length>0?required_array:null);
};
/** /REQUIRED INPUT (VALIDATE FORM) ***/


