<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"> Nueva Actualización</div>

                <div class="card-body">
                    <div class="alert alert-success" role="alert">
                        <p></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>
    $(document).ready(function () {
        window.Echo.channel('stock-disponible')
            .listen('StockDisponible', (data) => {
                $(".alert-success > p")
                    .empty()
                    .append('El ' + data.product.nombre + ' ahora tiene stock.');
            });
    });
</script>
