// Auto add data-label attributes to tbody cells for .table-auto-stack tables
(function(){
  function enhance(table){
    if(!table || table.dataset.enhanced) return;
    const heads = Array.from(table.querySelectorAll('thead th'));
    const labels = heads.map(h=> (h.textContent||'').trim());
    table.querySelectorAll('tbody tr').forEach(tr => {
      Array.from(tr.children).forEach((td,i)=>{
        if(td.hasAttribute('data-label')) return;
        if(labels[i]) td.setAttribute('data-label', labels[i]);
      });
    });
    table.dataset.enhanced = 'true';
  }
  function run(){
    document.querySelectorAll('table.table-auto-stack').forEach(enhance);
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', run); else run();
})();
