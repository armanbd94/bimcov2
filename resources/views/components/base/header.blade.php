@php 
    $products = \DB::table('products')->where('status',1)->whereColumn('alert_quantity','>','qty')->count();
    $materials = \DB::table('materials')->where('status',1)->whereColumn('alert_qty','>','qty')->count();
@endphp
<div id="kt_header" class="header  header-fixed ">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">

        </div>


        <div class="topbar">
            <div class="dropdown">

                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                    <div class="btn btn-icon btn-clean btn-dropdown btn-lg mr-1">
                        <span class="svg-icon svg-icon-xl svg-icon-primary">
                            <i class="fas fa-bell text-primary"></i>
                            @if ($products > 0 || $materials > 0)
                            <span class="badge badge-danger text-white" style="position: absolute;top:0;right:0;">{{ $products + $materials }}</span>
                            @endif
                            
                        </span>
                    </div>
                </div>


                <div  class="dropdown-menu p-0 m-0 dropdown-menu-right dropdown-menu-xl dropdown-menu-anim-up">
                    <form>
                        <div class="d-flex align-items-center justify-content-center py-10 px-8 bgi-size-cover bgi-no-repeat rounded-top bg-primary">
                            <h4 class="text-white m-0">Notification</h4>
                            <span type="button" class="badge badge-danger ml-3">
                                {{ $products + $materials }}
                            </span>
                        </div>


                        <div class="scroll pr-7 mr-n7 ps ps--active-y" data-scroll="true" data-height="250" data-mobile-height="200">
                            @if ($materials > 0 || $products > 0)
                            @if ($materials > 0)
                            <div class="pt-3">
                                <a href="{{ url('material-stock-alert-report') }}" class="text-danger"><div class="text-center font-weight-bolder" style="height: 80px;
                               width: 90%;
                               align-items: center;
                               display: flex;
                               justify-content: center;
                               margin: 0 auto;
                               box-shadow: 0px 0 5px rgba(0,0,0,0.4);">
                                {{ $materials }} materials are going to be out of stock
                               </div>
                            </a>
                            </div>
                            @endif
                            @if ($products > 0)
                            <div class="pt-3">
                                <a href="{{ url('stock-alert-report') }}" class="text-danger"><div class="text-center font-weight-bolder" style="height: 80px;
                               width: 90%;
                               align-items: center;
                               display: flex;
                               justify-content: center;
                               margin: 0 auto;
                               box-shadow: 0px 0 5px rgba(0,0,0,0.4);">
                                {{ $products }} products are going to be out of stock
                               </div></a>
                            </div>
                            @endif
                            @else 
                            <div class="p-8 text-center font-weight-bolder">
                            All caught up!
                            <br>No new notifications.
                            </div>
                            @endif
                        </div>
                        
                    </form>
                </div>
            </div>
            <div class="topbar-item">
                <div class="btn btn-icon w-auto btn-clean d-flex align-items-center btn-lg px-2"
                    id="kt_quick_user_toggle">
                    <span
                        class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Hi,</span>
                    <span
                        class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3">{{ Auth::user()->username }}</span>


                    <span class="symbol symbol-35 symbol-light-success">
                        <img class="header-profile-user" src="images/profile.svg" alt="Header Avatar">
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>