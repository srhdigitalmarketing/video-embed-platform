(function(){
const V=window.VEP||{}, video=document.getElementById('video');
let started=false,lastAd=0,adCooldown=30000;
function initSource(){
  if(!video||!V.source)return;
  const isHls=/\.m3u8($|\?)/i.test(V.source);
  if(isHls && window.Hls && Hls.isSupported()){
    const hls=new Hls({enableWorker:true});
    hls.loadSource(V.source);hls.attachMedia(video);
    hls.on(Hls.Events.ERROR,(e,d)=>{if(d?.fatal) console.error('HLS fatal error',d);});
  }else{
    video.src=V.source;
    video.addEventListener('error',()=>console.error('Video source error'),{once:true});
  }
}
function loadAd(){const now=Date.now();if(!V.adUrl||now-lastAd<adCooldown)return;lastAd=now;const s=document.createElement('script');s.src=V.adUrl+'?token='+encodeURIComponent(V.token)+'&t='+Date.now();s.async=true;document.body.appendChild(s);}
async function track(){try{await fetch(V.analytics,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token:V.token,watch_seconds:Math.floor(video.currentTime||0)})})}catch(e){}}
initSource();
video?.addEventListener('play',()=>{if(!started){started=true;track();loadAd();}});
video?.addEventListener('pause',track);video?.addEventListener('ended',track);
document.addEventListener('click',e=>{if(e.target.closest('video'))loadAd();});
})();