(function(){
  // Jeśli już istnieje nasz niestandardowy interfejs (wygenerowany przez filtr PHP), przerwij
  if(document.querySelector('.preomar-qty-wrapper')) return;
  const form = document.querySelector('form.cart');
  if(!form) return;
  const originalWrap = form.querySelector('.quantity');
  const qtyInput = form.querySelector('input.qty');
  if(!originalWrap || !qtyInput) return;

  const max = parseInt(qtyInput.getAttribute('max'),10);
  const min = parseInt(qtyInput.getAttribute('min'),10) || 1;
  if(isNaN(max)) return; // potrzebny limit aby pokazać styl allegro „z X sztuk”

  // Utwórz kontener
  const container = document.createElement('div');
  container.className = 'preomar-qty-wrapper';
  container.style.display='flex';
  container.style.alignItems='center';
  container.style.gap='10px';
  container.style.margin='12px 0 18px';

  // Label
  const label = document.createElement('div');
  label.textContent = 'Liczba sztuk';
  label.style.fontSize='.7rem';
  label.style.fontWeight='700';
  label.style.textTransform='uppercase';
  label.style.letterSpacing='.5px';
  label.style.color='#475569';
  // Usunięto absolute – powodowało nachodzenie przy naszym układzie
  label.style.display='block';
  label.style.margin='0 0 4px';

  // Box na przyciski i input
  const box = document.createElement('div');
  box.style.display='flex';
  box.style.alignItems='stretch';
  box.style.border='1px solid #cfd8e3';
  box.style.borderRadius='10px';
  box.style.overflow='hidden';
  box.style.background='#fff';
  box.style.boxShadow='0 1px 2px rgba(0,0,0,.04) inset';

  // Styl inputu
  qtyInput.style.border='none';
  qtyInput.style.width='58px';
  qtyInput.style.textAlign='center';
  qtyInput.style.fontWeight='600';
  qtyInput.style.fontSize='.9rem';
  qtyInput.style.padding='0';
  qtyInput.setAttribute('inputmode','numeric');
  qtyInput.setAttribute('pattern','[0-9]*');

  function makeBtn(sign){
    const b=document.createElement('button');
    b.type='button';
    b.textContent=sign;
    b.style.background='#f1f5f9';
    b.style.color='#0f172a';
    b.style.border='none';
    b.style.width='40px';
    b.style.fontSize='1rem';
    b.style.cursor='pointer';
    b.style.display='flex';
    b.style.alignItems='center';
    b.style.justifyContent='center';
    b.style.fontWeight='700';
    b.addEventListener('mouseenter',()=>{b.style.background='#e2e8f0';});
    b.addEventListener('mouseleave',()=>{b.style.background='#f1f5f9';});
    return b;
  }
  const minus = makeBtn('−');
  const plus  = makeBtn('+');

  box.appendChild(minus); box.appendChild(qtyInput); box.appendChild(plus);

  // Info „z X sztuk”
  const info = document.createElement('div');
  info.style.fontSize='.7rem';
  info.style.color='#64748b';
  info.style.whiteSpace='nowrap';

  container.appendChild(box);
  container.appendChild(info);
  // Wstawiamy label przed kontenerem
  originalWrap.replaceWith(label, container);

  function clamp(val){
    val = isNaN(val)?min:val;
    if(val<min) val=min;
    if(val>max) val=max;
    return val;
  }
  const lowThreshold = 5; // poniżej lub równy – ostrzeżenie
  function format(n){
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'\u00A0'); // spacja twarda jako separator tysięcy
  }
  function refresh(){
    qtyInput.value = clamp(parseInt(qtyInput.value,10));
    const stockTxt = format(max)+' sztuk';
  // Usunięto komunikat "Ostatnie sztuki!" przy niskim stanie magazynowym
  info.innerHTML = '<span style="color:#475569;">z '+stockTxt+'</span>';
    minus.disabled = parseInt(qtyInput.value,10)<=min;
    plus.disabled  = parseInt(qtyInput.value,10)>=max;
    minus.style.opacity = minus.disabled?'.45':'1';
    plus.style.opacity  = plus.disabled?'.45':'1';
    if(max<=lowThreshold){
      box.style.borderColor = '#f59e0b';
    }
  }
  minus.addEventListener('click',()=>{qtyInput.value=clamp(parseInt(qtyInput.value,10)-1); refresh();});
  plus.addEventListener('click',()=>{qtyInput.value=clamp(parseInt(qtyInput.value,10)+1); refresh();});
  qtyInput.addEventListener('input',refresh);
  qtyInput.addEventListener('change',refresh);
  refresh();
})();
