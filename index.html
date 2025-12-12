<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QASO MARKET</title>
    <style>
        /* --- CSS PROFESIONAL --- */
        :root { --primary: #2563eb; --bg: #f8fafc; --surface: #ffffff; --text: #1e293b; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding-bottom: 50px; }
        
        /* Navbar */
        .navbar { background: var(--primary); padding: 1rem 1.5rem; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 50; }
        .brand { font-weight: 700; font-size: 1.25rem; letter-spacing: 0.5px; display: flex; align-items: center; gap: 10px; }
        .nav-btn { background: rgba(255,255,255,0.2); border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; transition: 0.2s; font-weight: 500; font-size: 0.9rem; }
        .nav-btn:hover { background: rgba(255,255,255,0.3); }

        .container { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        .hidden { display: none !important; }

        /* Search */
        .search-container { margin-bottom: 2rem; position: relative; }
        .search-input { width: 100%; padding: 1rem 1rem 1rem 3rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); box-sizing: border-box; }
        .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .card { background: var(--surface); border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1); transition: transform 0.2s; border: 1px solid #f1f5f9; display: flex; flex-direction: column; }
        .card:hover { transform: translateY(-4px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .card h3 { margin: 0 0 0.5rem 0; color: #0f172a; font-size: 1.1rem; }
        .price { font-size: 1.5rem; font-weight: 700; color: var(--primary); margin: 0.5rem 0; }
        .stock { display: inline-block; background: #f1f5f9; color: #64748b; font-size: 0.85rem; padding: 0.25rem 0.75rem; border-radius: 999px; margin-bottom: 1rem; }

        /* Buttons */
        button { cursor: pointer; }
        .btn-primary { background: var(--primary); color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; width: 100%; transition: 0.2s; margin-top: auto; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-primary:disabled { background: #cbd5e1; cursor: not-allowed; }
        .btn-success { background: #10b981; color: white; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 600; width: 100%; }
        
        /* Modals */
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal { background: white; padding: 2rem; border-radius: 16px; width: 90%; max-width: 400px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative; animation: slideIn 0.3s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .close-btn { position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; }
        
        input, textarea { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 1rem; box-sizing: border-box; font-family: inherit; }
        .msg-error { color: #ef4444; text-align: center; font-size: 0.9rem; margin-bottom: 1rem; }

        /* Table */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #f1f5f9; }
        th { background: var(--primary); color: white; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="brand">🛍️ QASO MARKET</div>
        <div id="userNav">
            <button class="nav-btn" onclick="openAuth('login')">Acceder</button>
        </div>
    </nav>

    <div id="viewShop" class="container">
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" id="search" class="search-input" placeholder="Buscar productos..." onkeyup="renderGrid()">
        </div>
        <div id="grid" class="grid">
            <div style="text-align:center; grid-column: 1/-1;">Cargando sistema...</div>
        </div>
    </div>

    <div id="viewHistory" class="container hidden">
        <button class="nav-btn" style="background:#64748b; margin-bottom:1rem" onclick="toggleView('shop')">⬅ Volver al catálogo</button>
        <h2>📦 Mis Pedidos</h2>
        <div style="overflow-x:auto">
            <table><thead><tr><th>Fecha</th><th>Producto</th><th>Cant.</th><th>Total</th></tr></thead><tbody id="historyBody"></tbody></table>
        </div>
    </div>

    <div id="modalAuth" class="overlay hidden">
        <div class="modal">
            <button class="close-btn" onclick="closeModal('modalAuth')">&times;</button>
            <h2 id="authTitle" style="text-align:center; margin-top:0; color:var(--primary)">Ingresar</h2>
            <div id="authError" class="msg-error"></div>
            <input type="text" id="uUser" placeholder="Usuario">
            <input type="password" id="uPass" placeholder="Contraseña">
            <button class="btn-primary" onclick="submitAuth()">CONTINUAR</button>
            <p style="text-align:center; font-size:0.9rem; margin-top:1rem; cursor:pointer; color:var(--primary)" onclick="toggleAuth()">Cambiar a Registro/Login</p>
        </div>
    </div>

    <div id="modalCheckout" class="overlay hidden">
        <div class="modal">
            <button class="close-btn" onclick="closeModal('modalCheckout')">&times;</button>
            <h2 style="text-align:center; margin-top:0; color:var(--primary)">Confirmar Pedido</h2>
            <p style="text-align:center; margin-bottom:1.5rem">Producto: <strong id="cProd"></strong></p>
            
            <label style="font-size:0.85rem; font-weight:600">Teléfono de contacto:</label>
            <input type="text" id="cPhone" placeholder="+54 9 ...">
            
            <label style="font-size:0.85rem; font-weight:600">Dirección de envío:</label>
            <textarea id="cAddr" rows="2" placeholder="Calle, número, ciudad..."></textarea>
            
            <div id="cTotal" style="text-align:right; font-weight:bold; font-size:1.2rem; margin-bottom:1rem; color:var(--primary)"></div>
            <button class="btn-success" onclick="doBuy()">✔ CONFIRMAR Y ENVIAR</button>
        </div>
    </div>

    <script>
        const API = 'api.php?action=';
        let PRODUCTS = [];
        let SESSION = null;
        let AUTH_MODE = 'login';
        let SELECTED_PROD = null;

        window.onload = async () => {
            await checkSession();
            await loadCatalog();
        };

        // --- CORE ---
        async function checkSession() {
            try {
                const res = await fetch(API + 'check_session');
                const data = await res.json();
                const nav = document.getElementById('userNav');
                
                if (data.logged_in) {
                    SESSION = data;
                    
                    // LÓGICA DEL BOTÓN ADMIN
                    let adminBtn = '';
                    if (data.role === 'ADMIN') {
                        adminBtn = `<button class="nav-btn" onclick="window.location.href='admin.html'" style="background:#10b981; margin-right:5px">⚙️ Panel Admin</button>`;
                    }

                    nav.innerHTML = `
                        <span style="margin-right:10px">Hola, ${data.username}</span>
                        ${adminBtn}
                        <button class="nav-btn" onclick="loadHistory()" style="margin:0 5px">Mis Pedidos</button> 
                        <button class="nav-btn" onclick="logout()" style="background:#ef4444">Salir</button>
                    `;
                    closeModal('modalAuth');
                } else {
                    SESSION = null;
                    nav.innerHTML = `<button class="nav-btn" onclick="openAuth('login')">Acceder</button>`;
                }
                renderGrid();
            } catch (e) { console.error(e); }
        }

        async function loadCatalog() {
            try {
                const res = await fetch(API + 'get_catalog');
                if (!res.ok) throw new Error("Error HTTP: " + res.status);
                
                const data = await res.json();
                if (data.status === 'error') throw new Error(data.message);
                
                PRODUCTS = data;
                renderGrid();
            } catch (e) {
                document.getElementById('grid').innerHTML = `<div style="grid-column:1/-1; text-align:center; color:red">Error cargando catálogo: ${e.message}</div>`;
            }
        }

        function renderGrid() {
            const grid = document.getElementById('grid');
            const term = document.getElementById('search').value.toLowerCase();
            
            if (!PRODUCTS || PRODUCTS.length === 0) {
                grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:2rem; color:#64748b">No hay productos disponibles.</div>';
                return;
            }

            const filtered = PRODUCTS.filter(p => p.nombre.toLowerCase().includes(term));
            
            if(filtered.length === 0) {
                grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; padding:2rem; color:#64748b">No se encontraron coincidencias.</div>';
                return;
            }

            grid.innerHTML = filtered.map(p => `
                <div class="card">
                    <h3>${p.nombre}</h3>
                    <span class="stock">Stock: ${p.stock_actual} ${p.unidad || 'u'}</span>
                    <div class="price">$${parseFloat(p.precio_venta).toLocaleString()}</div>
                    <button class="btn-primary" 
                        ${!SESSION ? 'disabled title="Inicia sesión para comprar"' : ''} 
                        onclick="initCheckout('${p.codigo}')">
                        ${SESSION ? 'COMPRAR' : 'INICIA SESIÓN'}
                    </button>
                </div>
            `).join('');
        }

        // --- CHECKOUT ---
        function initCheckout(code) {
            SELECTED_PROD = PRODUCTS.find(p => p.codigo === code);
            if (!SELECTED_PROD) return;
            
            document.getElementById('cProd').innerText = SELECTED_PROD.nombre;
            document.getElementById('cTotal').innerText = '$' + parseFloat(SELECTED_PROD.precio_venta).toLocaleString();
            document.getElementById('modalCheckout').classList.remove('hidden');
        }

        async function doBuy() {
            const phone = document.getElementById('cPhone').value;
            const addr = document.getElementById('cAddr').value;
            
            if (!phone || !addr) { alert("Completa los datos de envío."); return; }
            if (!confirm("¿Confirmar compra?")) return;

            // Bloquear botón
            const btn = document.querySelector('#modalCheckout .btn-success');
            const originalText = btn.innerText;
            btn.disabled = true;
            btn.innerText = "Procesando...";

            try {
                const res = await fetch(API + 'registrar_movimiento', {
                    method: 'POST',
                    body: JSON.stringify({ 
                        codigo: SELECTED_PROD.codigo, 
                        cantidad: 1,
                        telefono: phone,
                        direccion: addr
                    })
                });
                const data = await res.json();
                
                if (data.status === 'success') {
                    alert("✅ ¡Compra exitosa! Te contactaremos.");
                    closeModal('modalCheckout');
                    loadCatalog();
                    document.getElementById('cPhone').value = '';
                    document.getElementById('cAddr').value = '';
                } else {
                    alert("❌ Error: " + data.message);
                }
            } catch (e) { alert("Error de conexión"); }
            finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        }

        // --- AUTH & HISTORY ---
        function openAuth(mode) {
            AUTH_MODE = mode;
            document.getElementById('authTitle').innerText = mode === 'login' ? 'Ingresar' : 'Crear Cuenta';
            document.getElementById('modalAuth').classList.remove('hidden');
            document.getElementById('authError').innerText = '';
        }
        
        function toggleAuth() {
            openAuth(AUTH_MODE === 'login' ? 'register' : 'login');
        }

        async function submitAuth() {
            const u = document.getElementById('uUser').value;
            const p = document.getElementById('uPass').value;
            if (!u || !p) { document.getElementById('authError').innerText = "Completa los campos"; return; }

            try {
                const res = await fetch(API + AUTH_MODE, { method: 'POST', body: JSON.stringify({username: u, password: p}) });
                const data = await res.json();
                if (data.status === 'success') {
                    if (AUTH_MODE === 'register') { alert("Cuenta creada. Ahora inicia sesión."); toggleAuth(); }
                    else checkSession();
                } else {
                    document.getElementById('authError').innerText = data.message;
                }
            } catch (e) { document.getElementById('authError').innerText = "Error de red"; }
        }

        async function loadHistory() {
            toggleView('history');
            const t = document.getElementById('historyBody');
            t.innerHTML = '<tr><td colspan="4">Cargando...</td></tr>';
            try {
                const res = await fetch(API + 'get_my_purchases');
                const data = await res.json();
                t.innerHTML = data.length ? data.map(r => `<tr><td>${new Date(r.fecha).toLocaleDateString()}</td><td>${r.producto}</td><td>${parseFloat(r.cantidad)}</td><td style="color:var(--primary); font-weight:bold">$${parseFloat(r.valor_total).toLocaleString()}</td></tr>`).join('') : '<tr><td colspan="4" style="padding:1rem; text-align:center">Aún no has realizado compras.</td></tr>';
            } catch (e) { t.innerHTML = '<tr><td colspan="4">Error de conexión.</td></tr>'; }
        }

        async function logout() { await fetch(API + 'logout'); checkSession(); toggleView('shop'); }
        function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
        function toggleView(v) { 
            document.getElementById('viewShop').classList.toggle('hidden', v !== 'shop');
            document.getElementById('viewHistory').classList.toggle('hidden', v !== 'history');
        }
    </script>
</body>
</html>