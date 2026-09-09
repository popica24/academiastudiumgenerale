(function(){
  // Auditul măsoară pagina în repaus, nu la mijlocul unei animații. AOS ține
  // elementele translatate și transparente până le vede intrând în ecran, ceea
  // ce ar da suprapuneri și ieșiri din ecran care nu există pentru un om.
  // Le aducem la locul lor înainte de orice măsurătoare.
  document.documentElement.classList.add('fara-aos');
  Array.prototype.forEach.call(document.querySelectorAll('[data-aos]'), function(el){
    el.removeAttribute('data-aos');
    el.removeAttribute('data-aos-delay');
  });

  function parse(c){
    var m=c.match(/rgba?\(([\d.]+)[,\s]+([\d.]+)[,\s]+([\d.]+)(?:[,\s/]+([\d.]+))?/);
    if(!m) return null;
    return [+m[1],+m[2],+m[3], m[4]===undefined?1:+m[4]];
  }
  function over(fg,bg){ // composite fg(with alpha) over bg
    var a=fg[3]; return [fg[0]*a+bg[0]*(1-a), fg[1]*a+bg[1]*(1-a), fg[2]*a+bg[2]*(1-a),1];
  }
  function lum(c){
    var f=function(v){v/=255; return v<=0.03928? v/12.92 : Math.pow((v+0.055)/1.055,2.4);};
    return 0.2126*f(c[0])+0.7152*f(c[1])+0.0722*f(c[2]);
  }
  function cr(a,b){var l1=lum(a),l2=lum(b); if(l1<l2){var t=l1;l1=l2;l2=t;} return (l1+0.05)/(l2+0.05);}
  function hex(c){return '#'+[0,1,2].map(function(i){return ('0'+Math.round(c[i]).toString(16)).slice(-2);}).join('').toUpperCase();}

  function effBg(el){
    var stack=[], n=el;
    while(n && n.nodeType===1){
      var c=parse(getComputedStyle(n).backgroundColor);
      if(c && c[3]>0){ stack.push(c); if(c[3]===1) break; }
      n=n.parentElement;
    }
    var base=[255,255,255,1];
    for(var i=stack.length-1;i>=0;i--) base=over(stack[i],base);
    return base;
  }
  function hasOwnText(el){
    for(var i=0;i<el.childNodes.length;i++){
      var n=el.childNodes[i];
      if(n.nodeType===3 && n.textContent.trim().length>1) return true;
    }
    return false;
  }

  // Un <details> închis nu mai arată nimic din conținutul lui, dar Chrome nu-i
  // pune display:none copiilor ca să-i ascundă, îi ascunde prin alt mecanism,
  // iar getClientRects tot le găsește cutii acolo unde ochiul nu vede nimic.
  // Fără filtrul ăsta, un răspuns de acordeon închis pare că se calcă cu
  // SUMMARY-ul acordeonului următor, deși niciunul din ele nu se vede. Sar
  // doar peste conținutul, nu și peste SUMMARY-ul care rămâne vizibil cât
  // acordeonul e închis.
  function ascunsDeDetailsInchis(el){
    for(var n=el.parentElement; n; n=n.parentElement){
      if(n.tagName==='DETAILS' && !n.hasAttribute('open')){
        var rezumat=n.querySelector('summary');
        if(rezumat && (rezumat===el || rezumat.contains(el))) continue;
        return true;
      }
    }
    return false;
  }

  // Un element care trăiește într-un container ce taie sau derulează pe
  // orizontală nu poate împinge pagina în lături: fie e decupat (hidden,
  // clip), fie ajunge la el derularea containerului (auto, scroll).
  function inScroller(el){
    for(var n=el.parentElement; n && n!==document.body; n=n.parentElement){
      var ox=getComputedStyle(n).overflowX;
      if(ox==='auto'||ox==='scroll'||ox==='hidden'||ox==='clip') return true;
    }
    return false;
  }

  var out={contrast:[],overflow:[],clipped:[],overlap:[],docScroll:null,tiny:[]};
  var els=document.querySelectorAll('body *');

  for(var i=0;i<els.length;i++){
    var el=els[i], st=getComputedStyle(el), r=el.getBoundingClientRect();
    if(st.display==='none'||st.visibility==='hidden'||r.width===0||r.height===0) continue;

    if(hasOwnText(el)){
      var fg=parse(st.color); if(!fg) continue;
      var bg=effBg(el);
      var c=over(fg,bg);
      var ratio=cr(c,bg);
      var size=parseFloat(st.fontSize);
      var w=st.fontWeight; var bold=(w==='bold'||parseInt(w,10)>=700);
      var large=(size>=24)||(bold&&size>=18.66);
      var need=large?3:4.5;
      if(ratio<need){
        out.contrast.push({
          sel: el.tagName.toLowerCase()+(el.className&&typeof el.className==='string'?'.'+el.className.trim().split(/\s+/).join('.'):''),
          text: el.textContent.trim().slice(0,48),
          ratio: Math.round(ratio*100)/100, need:need, size:size, weight:w,
          fg:hex(c), bg:hex(bg)
        });
      }
      if(size<12) out.tiny.push({sel:el.tagName.toLowerCase()+'.'+el.className, size:size, text:el.textContent.trim().slice(0,30)});
    }

    // clipped content
    if(el.scrollWidth > el.clientWidth+1 && st.overflowX==='visible' && !ascunsDeDetailsInchis(el)){
      out.clipped.push({sel:el.tagName.toLowerCase()+(typeof el.className==='string'&&el.className?'.'+el.className.trim().split(/\s+/).join('.'):''),
        scrollW:el.scrollWidth, clientW:el.clientWidth, text:el.textContent.trim().slice(0,40)});
    }
    // element sticking out of the viewport horizontally,
    // ignorând ce trăiește într-un container derulabil pe orizontală (carusel)
    if((r.right > window.innerWidth+2 || r.left < -2) && !inScroller(el) && !ascunsDeDetailsInchis(el)){
      out.overflow.push({sel:el.tagName.toLowerCase()+(typeof el.className==='string'&&el.className?'.'+el.className.trim().split(/\s+/).join('.'):''),
        left:Math.round(r.left), right:Math.round(r.right), vw:window.innerWidth, text:el.textContent.trim().slice(0,40)});
    }
  }

  // text-vs-text overlap between non-related elements
  var txt=[];
  for(var i=0;i<els.length;i++){
    var el=els[i], st=getComputedStyle(el);
    if(st.display==='none'||st.visibility==='hidden') continue;
    if(st.position==='absolute'||st.position==='fixed') continue;
    if(!hasOwnText(el)) continue;
    if(ascunsDeDetailsInchis(el)) continue;
    // Cutiile de linie, nu dreptunghiul care le înconjoară. Un <span> care se
    // rupe pe două rânduri are un getBoundingClientRect care acoperă și golul
    // de la capătul primului rând: doi vecini de pe același rând ar părea că
    // se calcă, deși textul lor nu se atinge nicăieri.
    var cutii=[], rl=el.getClientRects();
    for(var k=0;k<rl.length;k++){ if(rl[k].width>=4 && rl[k].height>=4) cutii.push(rl[k]); }
    if(!cutii.length) continue;
    txt.push({el:el,cutii:cutii});
  }
  for(var i=0;i<txt.length;i++){
    for(var j=i+1;j<txt.length;j++){
      var A=txt[i],B=txt[j];
      if(A.el.contains(B.el)||B.el.contains(A.el)) continue;
      var maxOx=0, maxOy=0;
      for(var a1=0;a1<A.cutii.length;a1++){
        for(var b1=0;b1<B.cutii.length;b1++){
          var ra=A.cutii[a1], rb=B.cutii[b1];
          var ox=Math.min(ra.right,rb.right)-Math.max(ra.left,rb.left);
          var oy=Math.min(ra.bottom,rb.bottom)-Math.max(ra.top,rb.top);
          if(ox>3&&oy>3&&ox*oy>maxOx*maxOy){ maxOx=ox; maxOy=oy; }
        }
      }
      if(maxOx>3&&maxOy>3){
        out.overlap.push({a:A.el.tagName+'.'+A.el.className+' ['+A.el.textContent.trim().slice(0,26)+']',
                          b:B.el.tagName+'.'+B.el.className+' ['+B.el.textContent.trim().slice(0,26)+']',
                          ox:Math.round(maxOx), oy:Math.round(maxOy)});
      }
    }
  }
  out.docScroll={scrollW:document.documentElement.scrollWidth, clientW:document.documentElement.clientWidth};
  var pre=document.createElement('pre'); pre.id='AUDIT';
  pre.textContent='@@AUDIT@@'+JSON.stringify(out)+'@@END@@';
  document.body.appendChild(pre);
})();
