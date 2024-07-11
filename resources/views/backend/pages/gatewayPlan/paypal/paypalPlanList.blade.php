<table class="table tt-footable align-middle" data-use-parent-width="true">
    <thead>
        <tr>
            <th class="text-center">{{ localize('S/L') }}</th>
            <th>{{ localize('Plan ID') }}</th>
            <th>{{ localize('Product ID') }}</th>
            <th>{{ localize('Name') }}</th>
            <th>{{ localize('Status') }}</th>
            <th data-breakpoints="xs sm">{{ localize('Created Date') }}</th>                          
            <th data-breakpoints="xs sm">{{ localize('Action') }}</th>                          
        
        </tr>
    </thead>
    <tbody>
        @if(!empty($gateWaysProducts))
            @foreach ($gateWaysProducts as $key => $plan)
           
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="text-center fs-sm">
                        {{ $plan->billing_id }}
                    </td>
                    <td>{{ $plan->product_id }}</td>
                    <td>{{ $plan->package_name }}</td>
                
                    <td>
                        @if($plan->is_active == 1)
                        <span class="badge bg-soft-success rounded-pill text-lowercase">
                            {{ localize('active') }} </span>
                        @else  
                        <span class="badge bg-soft-danger rounded-pill text-lowercase">
                            {{ localize('deactive') }} </span>
                        @endif
                        </td>
                    <td>{{ date('d-M-y h:i:s A', strtotime($plan->created_at)) }}</td>
                    <td class="text-end">
                        <div class="dropdown tt-tb-dropdown">
                            <button type="button" class="btn p-0" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow" style="">
                                <a class="dropdown-item" target="_blank"
                                    href="{{route('admin.subscription-settings.view.gateway.product', $plan->id)}}">
                                    <i data-feather="eye" class="me-2"></i>
                                    {{ localize('View') }}
                                </a>
                                
                                <a class="dropdown-item"
                                    href="{{route('admin.subscription-settings.delete.gateway.product', $plan->id)}}">
                                    <i data-feather="trash" class="me-2"></i>
                                    {{ localize('Delete') }}
                                </a>
                              
                                
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>