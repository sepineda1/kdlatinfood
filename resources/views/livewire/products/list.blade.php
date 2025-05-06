<div>
    <div class="row sales layout-top-spacing">

        <div class="col-sm-12">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h4 class="card-title">
                        <b>Lista De Productos </b>
                        <br>
                    </h4>
                   
                </div>

                <div class="widget-content">
                    <div class="row">
                            @foreach($categories2 as $category)
                            <div class="col-md-3">
                                <div class="" style="">
                                    <div class="card-header text-center" style="border:none;border-radius:15px;" >
                                        <h4 class=""><b>{{ $category->name }}</b></h4>
                                    </div>
                                    <div class="card-body text-center" style="background:#f7f7f7;">
                                        <img src="{{ asset('../storage/app/public/categories/' . $category->image) }}" width="50%" alt="">
                                    </div>
                                    <div class="card-body text-center" style="background:#f7f7f7;">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <a href="{{ url($category->id.'/raw/products') }}" class="btn btn-block"  style="border-radius:15px;border:none;color:white;background-image: linear-gradient(220deg, #ffb946 0, #ffa83d 16.67%, #fe9430 33.33%, #f37b1f 50%, #e86110 66.67%, #df480c 83.33%, #da2e11 100%);">Raw</a>
                                            </div>
                                            <div class="col-md-7">
                                                <a href="{{ url($category->id .'/precocked/products') }}" class="btn  btn-block"  style="border-radius:15px;border:none;color:white;background-image: linear-gradient(220deg, #ffb946 0, #ffa83d 16.67%, #fe9430 33.33%, #f37b1f 50%, #e86110 66.67%, #df480c 83.33%, #da2e11 100%);">PreCocked</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                            </div>
                            @endforeach
                        </div>
                </div>
            </div>
        </div>    
    </div>
   
</div>