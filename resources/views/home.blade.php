<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Productos</div>
                <div class="card-body">
                    @foreach($products as $product)
                        <form action="{{route('actualizar')}}" method="post">
                            {{csrf_field()}}
                            <input type="hidden" name="id" value="{{$product->nombre}}">
                            <p>
                                {{$product->nombre}} - {{$product->precio}} - Stock <input
                                    type="text" name="stock" value="{{$product->stock}}">
                                <input type = "submit" value="Actualizar">
                            </p>
                        </form>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>



