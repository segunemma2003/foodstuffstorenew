<div className="w-full overflow-hidden">
    <div className="flex items-center justify-between mb-6">
      <h2 className="text-2xl font-light flex items-center">
        <ShoppingCart className="h-5 w-5 mr-2 opacity-70" />
        Cart Items
      </h2>
      <div className="text-sm text-muted-foreground">
        {{count($cartItems)}} {{count($cartItems) === 1 ? 'item' : 'items'}}
      </div>
    </div>

    @if(count($cartItems) < 1)
      <div className="flex flex-col items-center justify-center py-16 animate-fade-in">
        <div className="w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4 animate-float">
          <ShoppingCart className="h-8 w-8 text-muted-foreground opacity-50" />
        </div>
        <p className="text-lg text-muted-foreground mb-2">No items in this cart</p>
      </div>
    @else
      <div className="bg-white rounded-xl overflow-hidden shadow-sm border animate-scale-in">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b">
                <th className="text-left py-4 px-6 font-medium text-sm text-muted-foreground">Product</th>
                <th className="text-center py-4 px-4 font-medium text-sm text-muted-foreground">Quantity</th>
                <th className="text-right py-4 px-6 font-medium text-sm text-muted-foreground">Price</th>
                <th className="text-right py-4 px-6 font-medium text-sm text-muted-foreground">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($cartItems as $item)
                <tr

                  className="border-b last:border-0 transition-colors hover:bg-muted/50"
                >
                  <td className="py-4 px-6">
                    <div className="flex items-center">
{{dd($item)}}
                        <div className="w-12 h-12 rounded-md overflow-hidden mr-4 bg-muted flex-shrink-0">
                          <img
                            src="{{$item->product->image}}"
                            alt="{{$item->product->name}}"
                            className="w-full h-full object-cover"
                          />
                        </div>

                      <div className="flex flex-col">
                        <span className="font-medium">{{$item->product->name}}</span>

                      </div>
                    </div>
                  </td>
                  <td className="py-4 px-4 text-center">
                    {{$item->quantity}}
                  </td>
                  <td className="py-4 px-6 text-right">
                    NGN  {{$item->price}}
                  </td>
                  <td className="py-4 px-6 text-right">
                    <span className="font-medium">
                     NGN {{ $item->price * $item->quantity}}
                    </span>
                  </td>
                </tr>

              @endforeach

            </tbody>

          </table>
        </div>
      </div>
      @endif
  </div>
