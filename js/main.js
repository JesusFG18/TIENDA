/* ===================================
   NOVEDADES ECONÓMICA - JAVASCRIPT
   Sin tallas
   =================================== */

document.addEventListener('DOMContentLoaded', function(){

    // Botones sumar/restar
    document.querySelectorAll('.btn-sumar').forEach(button => {
        button.addEventListener('click', function(){
            let input = this.parentElement.querySelector('input.cantidad-input');
            if(!input) return;
            let max = parseInt(input.getAttribute('max')) || 99;
            let valor = parseInt(input.value) || 1;
            if(valor < max){
                input.value = valor + 1;
            }
        });
    });

    document.querySelectorAll('.btn-restar').forEach(button => {
        button.addEventListener('click', function(){
            let input = this.parentElement.querySelector('input.cantidad-input');
            if(!input) return;
            let valor = parseInt(input.value) || 1;
            if(valor > 1){
                input.value = valor - 1;
            }
        });
    });

    // Vista rápida
    document.querySelectorAll('.btn-vista-rapida').forEach(btn => {
        btn.addEventListener('click', function(){
            const card = this.closest('.card-producto');
            if(!card) return;
            const nombre = card.getAttribute('data-nombre') || 'Producto';
            const precio = card.getAttribute('data-precio') || '0';
            const img = card.querySelector('.producto-img')?.src || '';
            const desc = card.getAttribute('data-desc') || 'Sin descripción';
            const material = card.getAttribute('data-material') || 'No especificado';
            let stock = '0';
            const cantidadInput = card.querySelector('.cantidad-input');
            if(cantidadInput){
                stock = cantidadInput.getAttribute('max');
            }
            if(document.getElementById('modal-nombre')) document.getElementById('modal-nombre').textContent = nombre;
            if(document.getElementById('modal-precio')) document.getElementById('modal-precio').textContent = '$' + parseFloat(precio).toFixed(2);
            if(document.getElementById('modal-img')) document.getElementById('modal-img').src = img;
            if(document.getElementById('modal-stock')) document.getElementById('modal-stock').textContent = '📦 ' + stock + ' disponibles';
            if(document.getElementById('modal-desc')) document.getElementById('modal-desc').textContent = desc;
            if(document.getElementById('modal-material')) document.getElementById('modal-material').textContent = material;
        });
    });

    // Agregar al carrito (sin talla)
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', function(){
            const card = this.closest('.card-producto');
            if(!card) return;
            
            const cantidadInput = card.querySelector('.cantidad-input');
            if(!cantidadInput) return;
            
            const cantidad = cantidadInput.value;
            const id = card.getAttribute('data-id');
            
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Agregando...';
            
            fetch('carrito/agregar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id_producto=${encodeURIComponent(id)}&cantidad=${encodeURIComponent(cantidad)}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    const badge = document.querySelector('.bi-cart3 + .badge');
                    if(badge) badge.textContent = data.total;
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Agregado';
                    this.classList.remove('btn-reservar');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-reservar');
                        this.disabled = false;
                        cantidadInput.value = 1;
                    }, 2000);
                } else {
                    alert('Error: ' + (data.error || 'No se pudo agregar al carrito'));
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
            });
        });
    });

    // Scroll suave
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if(href === '#') return;
            const target = document.querySelector(href);
            if(target){
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});