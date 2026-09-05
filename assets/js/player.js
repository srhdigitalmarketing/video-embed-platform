(function(){
const V=window.VEP||{}, video=document.getElementById('video'), layer=document.getElementById('ad-layer');
let started=false,lastAd=0,adCooldown=30000;
function loadAd(){const now=Date.now();if(now-lastAd<adCooldown)return;lastAd=now;const s=document.createElement('script');s.src=V.adUrl+'?token='+encodeURIComponent(V.token)+'&t='+Date.now();s.async=true;document.body.appendChild(s);}
async function track(){try{await fetch(V.analytics,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token:V.token,watch_seconds:Math.floor(video.currentTime||0)})})}catch(e){}}
video?.addEventListener('play',()=>{if(!started){started=true;track();loadAd();}});
video?.addEventListener('pause',track);video?.addEventListener('ended',track);
document.addEventListener('click',e=>{if(e.target.closest('video'))loadAd();});
})();