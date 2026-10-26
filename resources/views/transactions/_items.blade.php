{{--
    Baris item dinamis untuk transaksi.
    Variabel: $mode ('sale' | 'purchase'), $productOptions (array produk).
    Catatan: total di sini hanya ESTIMASI untuk pengguna. Server menghitung ulang total sebenarnya.
--}}
@php
    $initialItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
@endphp

<div class="table-responsive">
    <table class="table align-middle">
        <thead class="table-light">
        <tr>
            <th>Produk</th>
            <th style="width:130px">Jumlah</th>
            <th style="width:210px">{{ $mode === 'purchase' ? 'Harga Beli (Rp)' : 'Harga Jual' }}</th>
            <th class="text-end" style="width:170px">Subtotal</th>
            <th style="width:60px"></th>
        </tr>
        </thead>
        <tbody id="items-body"></tbody>
        <tfoot>
        <tr>
            <th colspan="3" class="text-end">Estimasi total</th>
            <th class="text-end" id="grand-total">Rp 0</th>
            <th></th>
        </tr>
        </tfoot>
    </table>
</div>

<button type="button" class="btn btn-outline-primary btn-sm" id="add-row">+ Tambah item</button>
<div class="form-text mt-2">
    Total akhir dihitung oleh server berdasarkan data di database.
</div>

@push('scripts')
<script>
(function () {
    const mode = @json($mode);
    const products = @json($productOptions);
    const initialItems = @json($initialItems);

    const tbody = document.getElementById('items-body');
    const totalEl = document.getElementById('grand-total');
    let index = 0;

    const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    const findProduct = (id) => products.find((p) => String(p.id) === String(id));

    function recalc() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((tr) => {
            const qty = parseInt(tr.querySelector('.qty').value, 10) || 0;
            let price = 0;
            if (mode === 'purchase') {
                price = parseFloat(tr.querySelector('.cost').value) || 0;
            } else {
                price = parseFloat(tr.dataset.price) || 0;
            }
            const sub = qty > 0 ? qty * price : 0;
            tr.querySelector('.subtotal').textContent = rupiah(sub);
            total += sub;
        });
        totalEl.textContent = rupiah(total);
    }

    function addRow(item) {
        item = item || {};
        const i = index++;
        const tr = document.createElement('tr');

        // Kolom produk
        const tdProduct = document.createElement('td');
        const select = document.createElement('select');
        select.name = 'items[' + i + '][product_id]';
        select.className = 'form-select product';
        select.required = true;
        select.appendChild(new Option('-- pilih produk --', ''));
        products.forEach((p) => {
            select.appendChild(new Option(p.label, p.id, false, String(p.id) === String(item.product_id)));
        });
        tdProduct.appendChild(select);

        // Kolom jumlah
        const tdQty = document.createElement('td');
        const qty = document.createElement('input');
        qty.type = 'number';
        qty.min = 1;
        qty.step = 1;
        qty.name = 'items[' + i + '][quantity]';
        qty.className = 'form-control qty';
        qty.required = true;
        qty.value = item.quantity || 1;
        tdQty.appendChild(qty);

        // Kolom harga
        const tdPrice = document.createElement('td');
        let cost = null;
        let priceText = null;
        if (mode === 'purchase') {
            cost = document.createElement('input');
            cost.type = 'number';
            cost.min = 0;
            cost.step = '0.01';
            cost.name = 'items[' + i + '][unit_cost]';
            cost.className = 'form-control cost';
            cost.required = true;
            if (item.unit_cost !== undefined && item.unit_cost !== '') {
                cost.value = item.unit_cost;
                cost.dataset.touched = '1';
            }
            cost.addEventListener('input', () => { cost.dataset.touched = '1'; recalc(); });
            tdPrice.appendChild(cost);
        } else {
            priceText = document.createElement('span');
            priceText.className = 'price';
            tdPrice.appendChild(priceText);
        }

        // Kolom subtotal
        const tdSub = document.createElement('td');
        tdSub.className = 'text-end subtotal';
        tdSub.textContent = 'Rp 0';

        // Kolom hapus
        const tdDel = document.createElement('td');
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-outline-danger';
        del.textContent = '\u00d7';
        del.title = 'Hapus baris';
        del.addEventListener('click', () => {
            tr.remove();
            if (tbody.querySelectorAll('tr').length === 0) { addRow(); }
            recalc();
        });
        tdDel.appendChild(del);

        function onProductChange() {
            const p = findProduct(select.value);
            if (mode === 'purchase') {
                if (p && !cost.dataset.touched) { cost.value = p.price; }
            } else {
                tr.dataset.price = p ? p.price : '';
                priceText.textContent = p ? rupiah(p.price) : '-';
                qty.max = p ? p.stock : '';
            }
            recalc();
        }

        select.addEventListener('change', onProductChange);
        qty.addEventListener('input', recalc);

        tr.append(tdProduct, tdQty, tdPrice, tdSub, tdDel);
        tbody.appendChild(tr);
        onProductChange();
    }

    document.getElementById('add-row').addEventListener('click', () => addRow());
    Object.values(initialItems).forEach((item) => addRow(item));
    if (tbody.querySelectorAll('tr').length === 0) { addRow(); }
})();
</script>
@endpush
