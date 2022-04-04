Cette méthode lance une animation de confettis sur l'objet <strong>confettiContainer_[confetti]</strong>
- après l'animation, la méthode confettiCallback est appelée.
- placer la vue <strong>confetti.html.twig</strong> dans le repértoire des views du projet
- dans <strong>confetti.html.twig</strong>, laisser les confettis choisis et numéroter les ID de 0 à X : <i>confettiModel_[n]</i>

<strong>[confetti]</strong> : numéro dans l'id de la div qui doit être animée (<i>confettiContainer_[confetti]</i> et, à l'intérieur, <i>confettiLaunchstep_[confetti]</i>
<br><strong>[quantity]</strong> : quantité de confettis
<br><strong>[distanceMin]</strong> : distance minimale en pixels de <i>confettiLaunchstep_[confetti]</i> où un confetti est lancé
<br><strong>[distanceMax]</strong> : distance minimale en pixels de <i>confettiLaunchstep_[confetti]</i> où un confetti est lancé
<br><strong>[duration]</strong> : durée du lancé (en millisecondes)
<br><strong>[number]</strong> : nombre de types de confettis dans confetti.html.twig
<br><strong>[width]</strong> : largeur de chaque confetti

<strong>Dans la vue appelant l'animation :</strong>

1 - Inclure dans la balise ```<head>``` 
```
<link rel="stylesheet" href="{{ f3.path }}ui/imi/plugins/confetti/confetti.css" type="text/css" />
<script type="text/javascript" src="{{ f3.path }}ui/imi/plugins/confetti/confetti.js"></script>
``` 
2 - Rajouter :
```
<div id="confettiContainer_1">
    <div id="confettiLaunchstep_1"><div class="confetti-launchstep"><img src="{{ f3.path }}project/playground/images/mini-site/rocket.svg" class="width-100"></div></div>
</div>
{% include ('confetti.html.twig') %}  
```
3 - Lancer l'animation : 
```
confetti(1,10,80,150,700,18,15);
```