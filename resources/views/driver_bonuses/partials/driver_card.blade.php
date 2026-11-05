{{--
    Point 104 - a driver's level, progress and bonuses, on the driver details
    pages (drivers/view, fleet_drivers/view). Filled by driver_card_scripts:
    users/{id}.driverLevel, driver_bonus_progress/{id}, driver_bonus_results.
--}}
<div class="row mt-4" id="driver_bonus_card">
    <div class="col-12">
        <div class="card border">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom pb-3 flex-wrap">
                <div class="card-header-title">
                    <h3 class="text-dark-2 mb-0 h4"><i class="mdi mdi-trophy mr-2"></i>{{ trans('lang.driver_bonus_card_title') }}</h3>
                </div>
                <div class="d-flex align-items-center">
                    <span class="mr-2 text-dark-2">{{ trans('lang.driver_level') }}:</span>
                    <span id="driver_level_badge" class="driver-level-badge level-bronze">{{ trans('lang.driver_level_bronze') }}</span>
                    <small id="driver_level_since" class="text-muted ml-2"></small>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <h5 class="mb-2">{{ trans('lang.driver_bonus_progress') }}</h5>
                        <div id="driver_bonus_progress" class="text-muted">{{ trans('lang.driver_bonus_loading') }}</div>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <h5 class="mb-2 d-flex justify-content-between">
                            <span>{{ trans('lang.driver_bonus_received') }}</span>
                            <span id="driver_bonus_total" class="text-dark-2"></span>
                        </h5>
                        <div id="driver_bonus_received" class="text-muted">{{ trans('lang.driver_bonus_loading') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .driver-level-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-weight: 700; font-size: 13px; }
    .driver-level-badge.level-bronze { background: #f3e3d3; color: #8a4b14; }
    .driver-level-badge.level-silver { background: #eceff1; color: #455a64; }
    .driver-level-badge.level-gold { background: #fff3c4; color: #8a6d00; }
    .driver-bonus-progress-row { border: 1px solid #eee; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
    .driver-bonus-progress-row .progress { height: 6px; margin: 6px 0 4px; }
    .driver-bonus-received-table td, .driver-bonus-received-table th { padding: 6px 8px; font-size: 13px; }
</style>
