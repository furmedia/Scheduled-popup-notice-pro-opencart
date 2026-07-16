<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-truck"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $text_signature; ?></div>
        <div class="well">
          <h4 style="margin-top:0;"><i class="fa fa-bolt"></i> <?php echo $text_performance_tools; ?></h4>
          <p><?php echo $text_performance_note; ?></p>
          <a href="<?php echo $clear_cache; ?>" data-toggle="tooltip" title="<?php echo $button_clear_cache; ?>" class="btn btn-warning"><i class="fa fa-refresh"></i> <?php echo $button_clear_cache; ?></a>
        </div>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $entry_banner_preview; ?></label>
            <div class="col-sm-10">
              <div style="position:relative;display:flex;align-items:center;justify-content:center;max-width:760px;min-height:560px;padding:36px;border:1px solid #ddd;border-radius:8px;background:rgba(37,24,42,.12);">
                <div style="position:relative;width:520px;max-width:100%;overflow:hidden;border:1px solid rgba(119,64,112,.2);border-radius:18px;background:#fffafc;box-shadow:0 22px 60px rgba(62,35,67,.24);text-align:center;">
                  <div style="position:absolute;right:12px;top:12px;z-index:3;display:flex;align-items:center;justify-content:center;width:44px;height:44px;border:3px solid #fff;border-radius:999px;background:#e21d2f;box-shadow:0 8px 20px rgba(126,18,31,.35);color:#fff;font-size:30px;font-weight:700;line-height:1;">&times;</div>
                  <div style="height:140px;overflow:hidden;position:relative;">
                    <img src="<?php echo $banner_preview; ?>" alt="" style="display:block;width:100%;height:100%;object-fit:cover;object-position:center;">
                    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,0),#fffafc 96%);"></div>
                  </div>
                  <div style="position:relative;z-index:2;margin-top:-24px;padding:0 38px 28px;">
                    <div style="display:inline-flex;align-items:center;gap:12px;min-height:64px;padding:9px 24px 9px 12px;border-radius:999px;background:linear-gradient(135deg,#462348,#64305d);box-shadow:0 10px 24px rgba(70,35,72,.22);color:#fff;">
                      <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:999px;background:#ead2dc;font-size:30px;">ðŸšš</span>
                      <strong style="font-size:26px;line-height:1;text-transform:uppercase;"><?php echo $module_cristale_shipping_notice_banner_title; ?></strong>
                    </div>
                    <div style="margin-top:18px;color:#6f3a68;font-size:27px;font-weight:700;line-height:1.24;text-transform:uppercase;"><?php echo $module_cristale_shipping_notice_banner_message; ?></div>
                    <div style="height:2px;width:80%;max-width:360px;margin:14px auto;background:linear-gradient(90deg,transparent,#d6aebf,transparent);"></div>
                    <div style="color:#2f2934;font-size:23px;font-weight:600;line-height:1.24;"><?php echo $module_cristale_shipping_notice_banner_submessage; ?></div>
                    <div style="margin:20px -38px -28px;padding:15px 24px;background:linear-gradient(135deg,#7b3d73,#8b4a7e);color:#fff;font-size:21px;font-style:italic;"><?php echo $text_preview_thanks; ?></div>
                  </div>
                </div>
              </div>
              <p class="help-block"><?php echo $text_banner_image_note; ?></p>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="module_cristale_shipping_notice_status" id="input-status" class="form-control">
                <?php if ($module_cristale_shipping_notice_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group<?php if ($error_timezone) { ?> has-error<?php } ?>">
            <label class="col-sm-2 control-label" for="input-timezone"><?php echo $entry_timezone; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_cristale_shipping_notice_timezone" value="<?php echo $module_cristale_shipping_notice_timezone; ?>" placeholder="Europe/Bucharest" id="input-timezone" class="form-control" />
              <?php if ($error_timezone) { ?>
              <div class="text-danger"><?php echo $error_timezone; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if ($error_starts_at) { ?> has-error<?php } ?>">
            <label class="col-sm-2 control-label" for="input-starts-at"><span data-toggle="tooltip" title="<?php echo $help_datetime; ?>"><?php echo $entry_starts_at; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="module_cristale_shipping_notice_starts_at" value="<?php echo $module_cristale_shipping_notice_starts_at; ?>" placeholder="2026-01-01 00:00:00" id="input-starts-at" class="form-control" />
              <?php if ($error_starts_at) { ?>
              <div class="text-danger"><?php echo $error_starts_at; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group<?php if ($error_ends_at) { ?> has-error<?php } ?>">
            <label class="col-sm-2 control-label" for="input-ends-at"><span data-toggle="tooltip" title="<?php echo $help_ends_at; ?>"><?php echo $entry_ends_at; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="module_cristale_shipping_notice_ends_at" value="<?php echo $module_cristale_shipping_notice_ends_at; ?>" placeholder="2026-01-02 00:00:00" id="input-ends-at" class="form-control" />
              <?php if ($error_ends_at) { ?>
              <div class="text-danger"><?php echo $error_ends_at; ?></div>
              <?php } ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-banner-title"><?php echo $entry_banner_title; ?></label>
            <div class="col-sm-10">
              <input type="text" name="module_cristale_shipping_notice_banner_title" value="<?php echo $module_cristale_shipping_notice_banner_title; ?>" id="input-banner-title" class="form-control" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-banner-message"><?php echo $entry_banner_message; ?></label>
            <div class="col-sm-10">
              <textarea name="module_cristale_shipping_notice_banner_message" rows="3" id="input-banner-message" class="form-control"><?php echo $module_cristale_shipping_notice_banner_message; ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-banner-submessage"><?php echo $entry_banner_submessage; ?></label>
            <div class="col-sm-10">
              <textarea name="module_cristale_shipping_notice_banner_submessage" rows="3" id="input-banner-submessage" class="form-control"><?php echo $module_cristale_shipping_notice_banner_submessage; ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-email-message"><span data-toggle="tooltip" title="<?php echo $help_email_message; ?>"><?php echo $entry_email_message; ?></span></label>
            <div class="col-sm-10">
              <textarea name="module_cristale_shipping_notice_email_message" rows="5" id="input-email-message" class="form-control"><?php echo $module_cristale_shipping_notice_email_message; ?></textarea>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>
