  <header class="admin-page-header">
    <h3>Game Controls</h3>
    <span class="admin-section--status">status_<span class="highlighted">OK</span></span>
  </header>


<div class="admin-sections">

  <section class="admin-box">
    <header class="admin-box-header">
      <h3>Countdown Timer</h3>
      <div class="admin-section-toggle radio-inline col">
        <input type="radio" name="fb--admin--countdown-timer" id="fb--admin--countdown-timer--on" checked="">
        <label for="fb--admin--countdown-timer--on">On</label>
        <input type="radio" name="fb--admin--countdown-timer" id="fb--admin--countdown-timer--off">
        <label for="fb--admin--countdown-timer--off">Off</label>
      </div>
    </header>
  </section>
   <section class="admin-box">
    <header class="admin-box-header">
      <h3>Registration</h3>
      <div class="admin-section-toggle radio-inline col">
        <input type="radio" name="fb--admin--registration" id="fb--admin--registration--on" checked="">
        <label for="fb--admin--registration--on">On</label>
        <input type="radio" name="fb--admin--registration" id="fb--admin--registration--off">
        <label for="fb--admin--registration--off">Off</label>
      </div>
    </header>
  </section>
  <section class="admin-box">
    <header class="admin-box-header">
      <h3>Levels</h3>
    </header>
  </section>
  <section class="admin-box">
    <header class="admin-box-header">
      <h3>Teams</h3>
    </header>
  </section>

  <div class="admin-box">

    <div class="fb-column-container global-controls-rules centered-columns">
      <div class="col col-1-3 col-pad">
        <div class="form-el el--select el--block-label">
          <label class="admin-label" for="">Team</label>
          <select>
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col col-1-3 col-pad">
        <div class="form-el el--select el--block-label">
          <label class="admin-label" for="">Level</label>
          <select>
            <option value="">Select</option>
          </select>
        </div>
      </div>
      <div class="col col-shrink col-pad admin-buttons">
        <button class="fb-cta">Score Level</button>
      </div>
    </div>


    <div class="global-controls-rules admin-row">

      <div class="form-el el--select el--block-label">
        <label class="admin-label" for="">Javascript Service</label>
        <select>
          <option value="">Select</option>
        </select>
      </div>
    </div>

    <div class="admin-buttons admin-row buttons-centered">
      <button class="fb-cta">Download Database Backup</button>
    </div>

  </div>
</div><!-- admin-sections -->