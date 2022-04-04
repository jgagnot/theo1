function confetti(confetti,quantity,distanceMin,distanceMax,duration,number=1,width=20) {

    var marginLeft=$('#confettiContainer_'+confetti+' .confetti-launchstep').width()/2;
    var marginTop=$('#confettiContainer_'+confetti+' .confetti-launchstep').height()/2;

    var confettiArray =[];
    var html=$('#confettiContainer_'+confetti).html();
    for (var i = 0; i < quantity; i++) {
        confettiArray[i]=[];
        confettiArray[i][0]=getRandomInt(number-1); //confetti type
        confettiArray[i][1]=getRandomInt(distanceMax,distanceMin); //confetti distance h
        confettiArray[i][3]=getRandomInt(confettiArray[i][1],distanceMin)*(getRandomInt(1)===0?-1:1); //confetti distance y
        confettiArray[i][2]= Math.sqrt(Math.pow(confettiArray[i][1],2) - Math.pow(confettiArray[i][3],2))*(getRandomInt(1)===0?-1:1); //confetti distance x
        confettiArray[i][4]=getRandomInt(360)*(getRandomInt(1)===0?-1:1); //confetti rotation
        confettiArray[i][5]=getRandomInt(10); //confetti couleur

        // une fois sur deux, on switche x et y pour éviter que x soit toujours < y
        if (getRandomInt(1)) {
            var x=confettiArray[i][2];
            confettiArray[i][2]=confettiArray[i][3];
            confettiArray[i][3]=x;
        }

        html += '<div id="confettiContainer_'+confetti+'_'+i+'" class="confetti" style="width:'+width+'px;transform: rotate(' + confettiArray[i][4] + 'deg);margin-top:'+((-0.65*i)-(i===0?marginTop:0))+'px;margin-left:'+marginLeft+'px;opacity: 0">' + $('#confettiModel_' + confettiArray[i][0]).html() + '</div>';
    }

    $('#confettiContainer_'+confetti).html(html);

    animate('#confettiLaunchstep_'+confetti,'heartBeat');
    setTimeout(function(){
        for (i = 0; i < quantity; i++) {
            $('#confettiContainer_'+confetti+'_'+i+' svg:first-child').addClass('confetti-color-'+confettiArray[i][5]);

            $('#confettiContainer_'+confetti+'_'+i).animate({
                    'opacity':1,
                    'top': confettiArray[i][3],
                    'left': confettiArray[i][2]
                }, getRandomInt(2)<2?duration:duration*1.7, 'easeOutQuad', //un tiers des confettis sont ralentis à 170 %
            );

        }
    }, 300);

}