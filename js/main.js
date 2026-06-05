/* ===================================
   NOVEDADES ECONÓMICA - JAVASCRIPT
   Funciones: +/-, Vista rápida, Agregar Carrito
   =================================== */

document.addEventListener('DOMContentLoaded', function(){

    // 1. BOTONES SUMAR CANTIDAD
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

    // 2. BOTONES RESTAR CANTIDAD
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

    // 3. VISTA RÁPIDA - LLENA EL MODAL CON DATOS DE LA CARD
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
            const stockBadge = card.querySelector('.badge');
            const cantidadInput = card.querySelector('.cantidad-input');
            
            if(cantidadInput){
                stock = cantidadInput.getAttribute('max');
            } else if(stockBadge){
                stock = stockBadge.textContent.match(/\d+/)?.[0] || '0';
            }
            
            if(document.getElementById('modal-nombre')){
                document.getElementById('modal-nombre').textContent = nombre;
            }
            if(document.getElementById('modal-precio')){
                document.getElementById('modal-precio').textContent = '$' + parseFloat(precio).toFixed(2);
            }
            if(document.getElementById('modal-img')){
                document.getElementById('modal-img').src = img;
            }
            if(document.getElementById('modal-stock')){
                document.getElementById('modal-stock').textContent = '📦 ' + stock + ' disponibles';
            }
            if(document.getElementById('modal-desc')){
                document.getElementById('modal-desc').textContent = desc;
            }
            if(document.getElementById('modal-material')){
                document.getElementById('modal-material').textContent = material;
            }
        });
    });

    // 4. AGREGAR AL CARRITO
    document.querySelectorAll('.btn-agregar').forEach(btn => {
        btn.addEventListener('click', function(){
            const card = this.closest('.card-producto');
            if(!card) return;
            
            const tallaSelect = card.querySelector('.talla-select');
            const cantidadInput = card.querySelector('.cantidad-input');
            
            if(!tallaSelect ||!cantidadInput) return;
            
            const talla = tallaSelect.value;
            const cantidad = cantidadInput.value;
            const id = card.getAttribute('data-id');
            
            // Validar que seleccionó talla
            if(!talla){
                tallaSelect.classList.add('is-invalid');
                alert('Por favor selecciona una talla');
                return;
            }
            
            tallaSelect.classList.remove('is-invalid');
            
            // Deshabilitar botón mientras procesa
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Agregando...';
            
            // Enviar al carrito
            fetch('carrito/agregar.php', { 
                method: 'POST', 
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `id_producto=${encodeURIComponent(id)}&cantidad=${encodeURIComponent(cantidad)}&talla=${encodeURIComponent(talla)}`
            })
          .then(res => {
                if(!res.ok) throw new Error('Error HTTP: ' + res.status);
                return res.json();
           })
          .then(data => {
                if(data.success){
                    // Actualizar contador del carrito
                    const badge = document.querySelector('.bi-cart3 +.badge');
                    if(badge){
                        badge.textContent = data.total;
                    }
                    
                    // Mensaje de éxito
                    this.innerHTML = '<i class="bi bi-check-circle"></i> Agregado';
                    this.classList.remove('btn-reservar');
                    this.classList.add('btn-success');
                    
                    // Resetear después de 2 segundos
                    setTimeout(() => {
                        this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                        this.classList.remove('btn-success');
                        this.classList.add('btn-reservar');
                        this.disabled = false;
                        cantidadInput.value = 1;
                        tallaSelect.value = '';
                    }, 2000);
                } else {
                    alert('Error: ' + (data.error || 'No se pudo agregar al carrito'));
                    this.disabled = false;
                    this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
                }
            })
          .catch(error => {
                console.error('Error:', error);
                alert('Error al conectar con el servidor. Revisa la consola F12');
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cart-plus"></i> Agregar';
            });
        });
    });

    // 5. SCROLL SUAVE AL DAR CLICK EN "Ver Colección"
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if(href === '#') return;
            
            const target = document.querySelector(href);
            if(target){
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

});