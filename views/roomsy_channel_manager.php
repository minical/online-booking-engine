<div class="settings integrations" integrations_enabled="<?=$integrations_enabled;?>">
	<div class="page-header">
		<h2>
			<?php echo l('Direct Connect', true); ?> 
		</h2>
	</div>
   
    <div>
        <?php if(isset($otas)) : foreach ($otas as $ota) : 
            $company_exist = $ota['company_status'] == 1 ? true : false;
            $is_configured = ($company_exist && ($ota['ota_hotel_id'] || $ota['username'] || $ota['password'])) ? true : false; 
            $integrations_status = $integrations_enabled ? "" : "disabled";
            ?>
            <div class="panel panel-default <?=($ota['ota_id'] == SOURCE_SITEMINDER) ? "hidden" : "";?>" id="ota-<?=$ota['ota_id'];?>">
                <div class="panel-heading">                    
                    <h3 class="panel-title bold"><?=$ota['name'];?></h3>
                </div>
                <div class="panel-body form-horizontal ">
                    <div id="configure-ota-<?=$ota['ota_id'];?>" class="<?=$is_configured ?'hidden' : ''?>">
                        <div class="form-group rate-group text-center">
                            <label for="ota_hotel_id" class="col-sm-3 control-label">
                                <?=$ota['name']?> <?php echo  l('hotel_id'); ?>
                            </label>
                            <div class="col-sm-9">
                                <input name="ota_hotel_id" type="text" class="form-control" value="<?=$ota['ota_hotel_id']?>" <?=$integrations_status;?> />
                            </div>
                        </div>
                        
                        <?php if($ota['ota_name'] != "booking_dot_com" && $ota['ota_name'] != "agoda"  && $ota['ota_name'] != "siteminder" ){ ?>
                        <div class="form-group rate-group text-center">
                            <label for="username" class="col-sm-3 control-label">
                                <?php echo l('username'); ?>
                            </label>
                            <div class="col-sm-9">
                                <input name="username" type="text" class="form-control" value="<?=$ota['username']?>" <?=$integrations_status;?> />
                            </div>
                        </div>
                        <div class="form-group rate-group text-center">
                            <label for="password" class="col-sm-3 control-label">
                                <?php echo l('password'); ?>
                            </label>
                            <div class="col-sm-9">
                                <input name="password" type="text" class="form-control" value="<?=$ota['password']?>" <?=$integrations_status;?> />
                            </div>
                        </div>
                        <?php } ?>
                        <div class="text-center">
                            <button class="btn btn-success configure-new-channel" data-ota_id="<?=$ota['ota_id']?>" <?=$integrations_status;?>><?php echo l('Save', true); ?></button>
                        </div>
                    </div>
                    <div id="manage-ota-<?=$ota['ota_id'];?>" class="<?=$is_configured ?'' : 'hidden'?>">
                        <div class="text-center">
                            <button class="btn btn-success manage-channel" data-ota_id="<?=$ota['ota_id']?>" <?=$integrations_status;?>><?=l("Map Room Types & Rates", true);?></button>
                            <button class="btn btn-warning edit-channel-configuration" data-ota_id="<?=$ota['ota_id']?>" <?=$integrations_status;?>><?=l("Account Setup", true);?></button>
                            <button class="btn btn-danger deconfigure-channel" data-ota_id="<?=$ota['ota_id']?>" <?=$integrations_status;?>><?=l("De-Configure");?></button>
                        </div>
                    </div>
                </div>
                
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>


