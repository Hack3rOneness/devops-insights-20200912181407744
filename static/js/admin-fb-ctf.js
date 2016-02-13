var $body = $('body');

/**
 * --admin
 */
 FB_CTF.admin = (function(){

  var PLAYERS_PER_TEAM = 1;

  /**
   * check the admin forms for errors
   *
   * @param $clicked (jquery object)
   *   - the clicked element. From this, we'll find the form
   *      elements we're looking to validate
   *
   * @return Boolean
   *   - whether or not the form is valud
   */
  function validateAdminForm( $clicked ){
    var valid         = true,
        $validateForm = $clicked.closest('.validate-form')
        $required     = $('.form-el--required', $validateForm),
        errorClass    = 'form-error';

    if( $validateForm.length === 0 ){
      $validateForm = $clicked.closest('.fb-admin-main');
    }

    $('.error-msg', $validateForm).remove();

    $required.removeClass(errorClass).each(function(){
    var $self       = $(this),
        $requiredEl = $('input[type="text"], input[type="password"]', $self ),
        $logoName   = $('.logo-name', $self);

        //
        // all the conditions that would make this element
        //  trigger an error
        //
        if(
          ( $requiredEl.val() === '' ) ||
          ( $logoName.length > 0 && $logoName.text() === '' )
        ) {
          $self.addClass( errorClass );
          valid = false;

          if($('.error-msg', $validateForm).length === 0){
            $('.admin-box-header h3', $validateForm).after('<span class="error-msg">Please fix the errors in red</span>');
          }

          return;
        }
      });

    return valid;
  }


  /**
   * add a new section
   *
   * @param $clicked (jquery object)
   *   - the clicked button
   */
  function addNewSection( $clicked ){
    var $sectionContainer = $clicked.closest('.admin-buttons').siblings('.admin-sections'),
        $lastSection      = $('.admin-box', $sectionContainer).last(),
        $firstSection      = $('.admin-box', $sectionContainer).first(),
        $newSection       = $firstSection.clone(),

        // +1 for the 0-based index, +1 for the new section
        //  being added
        sectionIndex      = $lastSection.index() + 1;

        //
        // update some stuff in the cloned section
        //
    var $title        = $('.admin-box-header h3', $newSection),
        titleText     = $title.text().toLowerCase(),
        switchName    = $('input[type="radio"]', $newSection).first().attr('name');

    if (switchName) {
      newSwitchName = switchName.substr( 0, switchName.lastIndexOf("--")) + "--" + sectionIndex;

      $('#' + switchName + '--on', $newSection).attr('id', newSwitchName + "--on");
      $('label[for="' + switchName + '--on"]', $newSection).attr('for', newSwitchName + "--on");
      $('#' + switchName + '--off', $newSection).attr('id', newSwitchName + "--off");
      $('label[for="' + switchName + '--off"]', $newSection).attr('for', newSwitchName + "--off");
      $('input[type="radio"]', $newSection).attr('name', newSwitchName);
    }

    $newSection.removeClass('section-locked');
    $newSection.removeClass('completely-hidden');

    $('.emblem-carousel li.active', $newSection).removeClass('active');
    $('.form-error', $newSection).removeClass('form-error');
    $('.post-avatar, .logo-name', $newSection).removeClass('has-avatar').empty();
    $('.error-msg', $newSection).remove();
    $('input[type="text"], input[type="password"]', $newSection).prop("disabled", false);

    $('.dk-select', $newSection).remove();

    $('select', $newSection).dropkick();

    if (titleText.indexOf('team') > -1) {
      $title.text('Team ' + sectionIndex);
    } else if( titleText.indexOf('quiz level') > -1){
      $title.text('Quiz Level ' + sectionIndex);
    } else if( titleText.indexOf('base level') > -1){
      $title.text('Base Level ' + sectionIndex);
    } else if( titleText.indexOf('flag level') > -1){
      $title.text('Flag Level ' + sectionIndex);
    } else if( titleText.indexOf('player') > -1){
      $title.text('Player ' + sectionIndex);
    }

    $('input[type="text"], input[type="password"]', $newSection).val('');

    $sectionContainer.append($newSection);

    FB_CTF.slider.init();
  }



  /**
   * render the registration page, updating text and values
   *  based on the number of players that have been set
   */
  function renderRegistrationPage(){
    var $sections = $('#fb-buildkit .admin-sections');

    if (PLAYERS_PER_TEAM > 1) {
      var $playerList = $('.player-list'),
          $playerInfo = $('li', $playerList);
      
      $('.admin-box-header h3', $sections).text("Team 1");
      $sections.addClass('team-registration');

      for (var i = 2; i <= PLAYERS_PER_TEAM; i++) {
        var $newRow = $playerInfo.clone();
        $('.player-list--label', $newRow).text("Player " + i + " Name");

        $playerList.append( $newRow );
      }
    }
  }

  /**
   * submits an ajax request to the admin endpoint
   *
   * @param  request_data (request object)
   *   - the parameters for the request.
   *
   * @return Boolean
   *   - whether or not the request was succesful
   */
  function sendAdminRequest(request_data) {
    $.post(
      'admin.php',
      request_data
    ).fail(function() {
      // TODO: Make this a modal
      console.log('ERROR');
    }).done(function(data) {
      console.log(data);
      var responseData = JSON.parse(data);
      if (responseData.result == 'OK') {
        console.log('OK');
      } else {
        // TODO: Make this a modal
        console.log('Failed');
      }
    });
  }

  // Generic deletion
  function deleteElement(section) {
    var elementSection = $('form', section)[0].classList[0];
    if (elementSection === 'session_form') {
      deleteSession(section);
    } else if (elementSection === 'team_form') {
      deleteTeam(section); 
    } else if (elementSection === 'level_form') {
      deleteLevel(section);
    }
  }

  // Generic update
  function updateElement(section) {
    var elementSection = $('form', section)[0].classList[0];
    if (elementSection === 'team_form') {
      updateTeam(section);
    } else if (elementSection === 'level_form') {
      updateLevel(section);
    }
  }

  // Generic create
  function createElement(section) {
    var elementSection = $('form', section)[0].classList[0];
    if (elementSection === 'team_form') {
      createTeam(section);
    } else if (elementSection === 'level_form') {
      createLevel(section);
    }
    location.reload();
  }

  // Delete level
  function deleteLevel(section) {
    var level_id = $('.level_form input[name=level_id]', section)[0].value;
    var delete_data = {
      action: 'delete_level',
      level_id: level_id
    };
    if (level_id) {
      sendAdminRequest(delete_data);
    }
  }

  // Create generic level
  function createLevel(section) {
    var level_type = $('.level_form input[name=level_type]', section)[0].value;
    switch (level_type) {
      case 'quiz':
        createQuizLevel(section);
        break;
      case 'flag':
        createFlagLevel(section);
        break;
      case 'base':
        createBaseLevel(section);
        break;
    }
  }

  // Create quiz level
  function createQuizLevel(section) {
    var question = $('.level_form input[name=question]', section)[0].value;
    var answer = $('.level_form input[name=answer]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var bonus_dec = $('.level_form input[name=bonus_dec]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var create_data = {
      action: 'create_quiz',
      question: question,
      answer: answer,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      bonus_dec: bonus_dec,
      hint: hint,
      penalty: penalty
    };
    if (question && answer && entity_id && points) {
      sendAdminRequest(create_data);
    }
  }

  // Create flag level
  function createFlagLevel(section) {
    var description = $('.level_form input[name=description]', section)[0].value;
    var flag = $('.level_form input[name=flag]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var bonus_dec = $('.level_form input[name=bonus_dec]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var create_data = {
      action: 'create_flag',
      description: description,
      flag: flag,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      bonus_dec: bonus_dec,
      hint: hint,
      penalty: penalty
    };
    if (description && flag && entity_id && points) {
      sendAdminRequest(create_data);
    }
  }

  // Create base level
  function createBaseLevel(section) {
    var description = $('.level_form input[name=description]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var create_data = {
      action: 'create_base',
      description: description,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      hint: hint,
      penalty: penalty
    };
    if (description && entity_id && points) {
      sendAdminRequest(create_data);
    }
  }

  // Update generic level
  function updateLevel(section) {
    var level_type = $('.level_form input[name=level_type]', section)[0].value;
    switch (level_type) {
      case 'quiz':
        updateQuizLevel(section);
        break;
      case 'flag':
        updateFlagLevel(section);
        break;
      case 'base':
        updateBaseLevel(section);
        break;
    } 
  }

  // Update quiz level
  function updateQuizLevel(section) {
    var question = $('.level_form input[name=question]', section)[0].value;
    var answer = $('.level_form input[name=answer]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var bonus_dec = $('.level_form input[name=bonus_dec]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var level_id = $('.level_form input[name=level_id]', section)[0].value;
    var update_data = {
      action: 'update_quiz',
      question: question,
      answer: answer,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      bonus_dec: bonus_dec,
      hint: hint,
      penalty: penalty,
      level_id: level_id
    };
    if (question && answer && entity_id && points) {
      sendAdminRequest(update_data);
    }
  }

  // Update flag level
  function updateFlagLevel(section) {
    var description = $('.level_form input[name=description]', section)[0].value;
    var flag = $('.level_form input[name=flag]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var bonus_dec = $('.level_form input[name=bonus_dec]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var level_id = $('.level_form input[name=level_id]', section)[0].value;
    var update_data = {
      action: 'update_flag',
      description: description,
      flag: flag,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      bonus_dec: bonus_dec,
      hint: hint,
      penalty: penalty,
      level_id: level_id
    };
    if (description && flag && entity_id && points) {
      sendAdminRequest(update_data);
    }
  }

  // Update base level
  function updateBaseLevel(section) {
    var description = $('.level_form input[name=description]', section)[0].value;
    var entity_id = $('.level_form select[name=entity_id] option:selected', section)[0].value;
    var points = $('.level_form input[name=points]', section)[0].value;
    var bonus = $('.level_form input[name=bonus]', section)[0].value;
    var hint = $('.level_form input[name=hint]', section)[0].value;
    var penalty = $('.level_form input[name=penalty]', section)[0].value;
    var level_id = $('.level_form input[name=level_id]', section)[0].value;
    var update_data = {
      action: 'update_base',
      description: description,
      entity_id: entity_id,
      points: points,
      bonus: bonus,
      hint: hint,
      penalty: penalty,
      level_id: level_id
    };
    if (description && entity_id && points) {
      sendAdminRequest(update_data);
    }
  }

  // Delete team
  function deleteTeam(section) {
    var team_id = $('.team_form input[name=team_id]', section)[0].value;
    var delete_data = {
      action: 'delete_team',
      team_id: team_id
    };
    if (team_id) {
      sendAdminRequest(delete_data);
    }
  }

  // Create team
  function createTeam(section) {
    var team_name = $('.team_form input[name=team_name]', section)[0].value;
    var team_password = $('.team_form input[name=password]', section)[0].value;
    var team_logo = $('.logo-name', section)[0].textContent;
    var create_data = {
      action: 'create_team',
      name: team_name,
      password: team_password,
      logo: team_logo
    };
    if (team_name && team_password && team_logo) {
      sendAdminRequest(create_data);
    }
  }

  // Update team
  function updateTeam(section) {
    var team_id = $('.team_form input[name=team_id]', section)[0].value;
    var team_name = $('.team_form input[name=team_name]', section)[0].value;
    var team_password = $('.team_form input[name=password]', section)[0].value;
    var team_password2 = $('.team_form input[name=password2]', section)[0].value;
    var team_logo = $('.logo-name', section)[0].textContent;
    var update_data = {
      action: 'update_team',
      team_id: team_id,
      name: team_name,
      password: team_password,
      password2: team_password2,
      logo: team_logo
    };
    if (team_id && team_name && team_password && team_password2 && team_logo) {
      sendAdminRequest(update_data);
    }
  }

  // Toggle team option
  function toggleTeam(radio_id) {
    var team_id = radio_id.split('--')[2].split('-')[1];
    var radio_action = radio_id.split('--')[2].split('-')[2];
    var action_value = (radio_id.split('--')[3] === 'on') ? 1 : 0;
    var toggle_data = {
      action: 'toggle_' + radio_action + '_team',
      team_id: team_id,
      [radio_action]: action_value
    };
    if (team_id && radio_action) {
      sendAdminRequest(toggle_data);
    }
  }

  // Toggle level option
  function toggleLevel(radio_id) {
    var level_id = radio_id.split('--')[2].split('-')[1];
    var radio_action = radio_id.split('--')[2].split('-')[2];
    var action_value = (radio_id.split('--')[3] === 'on') ? 1 : 0;
    var toggle_data = {
      action: 'toggle_' + radio_action + '_level',
      level_id: level_id,
      [radio_action]: action_value
    };
    if (level_id && radio_action) {
      sendAdminRequest(toggle_data);
    }
  }

  function toggleLogo(section) {
  // Toggle logo status
    var logo_id = $('.logo_form input[name=logo_id]', section)[0].value;
    var action_value = $('.logo_form input[name=status_action]', section)[0].value;
    var toggle_data = {
      action: action_value + '_logo',
      logo_id: logo_id
    };
    if (logo_id && action_value) {
      sendAdminRequest(toggle_data);
    }
    location.reload();
  }

  function toggleCountry(section) {
  // Toggle country status
    var country_id = $('.country_form input[name=country_id]', section)[0].value;
    var action_value = $('.country_form input[name=status_action]', section)[0].value;
    var toggle_data = {
      action: action_value + '_country',
      country_id: country_id
    };
    if (country_id && action_value) {
      sendAdminRequest(toggle_data);
    }
    location.reload();
  }

  // Delete session
  function deleteSession(section) {
    var session_cookie = $('.session_form input[name=cookie]', section)[0].value;
    var delete_data = {
      action: 'delete_session',
      cookie: session_cookie
    };
    console.log(delete_data);
    if (session_cookie) {
      sendAdminRequest(delete_data);
    }
  }

  /* --------------------------------------------
   * --init
   * -------------------------------------------- */


  /**
   * init the admin stuff
   */
  function init() {
    $body.off('content-loaded').on('content-loaded', function(event, data){
      if( data && data.page && data.page === 'registration'){
        renderRegistrationPage();
      }
    });

    //
    // actionable buttons
    //
    $('.fb-admin-main').off('click').on('click', '[data-action]', function(event) {
      event.preventDefault();
      var $self        = $(this),
          $section     = $self.closest('.admin-box'),
          action       = $self.data('action'),
          actionModal  = $self.data('actionModal'),
          lockClass    = 'section-locked',
          sectionTitle = $self.closest('#fb-buildkit').find('.admin-page-header h3').text().replace(' ', '_');

      //
      // route the actions
      //
      if (action === 'save') {
        var valid = validateAdminForm($self);

        if (actionModal && valid === false){
          actionModal = 'error';
        } else {
          updateElement($section);
        }
        if (valid) {
          $section.addClass(lockClass);
          $('input[type="text"], input[type="password"]', $section).prop("disabled", true);
        }
      } else if (action === 'save-no-validation'){
        updateElement($section);
      } else if (action === 'add-new'){
        addNewSection($self);
      } else if (action === 'create') {
        createElement($section);
      } else if (action === 'edit'){
        $section.removeClass(lockClass);
        $('input[type="text"], input[type="password"]', $section).prop("disabled", false);
      } else if (action === 'delete') {
        $section.remove();
        deleteElement($section);

        // rename the section boxes
        $('.admin-box').each(function(i, el){
          var $titleObj  = $('.admin-box-header h3', el),
               title     = $titleObj.text(),
               newTitle  = title.substring( 0, title.lastIndexOf(" ") + 1 ) + (i + 1);

          $titleObj.text(newTitle);
        });
      } else if (action === 'disable-logo') {
        toggleLogo($section);
      } else if (action === 'enable-logo') {
        toggleLogo($section);
      } else if (action === 'disable-country') {
        toggleCountry($section);
      } else if (action === 'enable-country') {
        toggleCountry($section);
      } 

      //
      // if there's a modal
      //
      if( actionModal ){
        FB_CTF.modal.loadPopup( 'action-' + actionModal , function(){
          $('#fb-modal .admin-section-name').text(sectionTitle);
        });
      }
    });

    //
    // radio buttons
    //
    $('input[type="radio"]').on('change', function(event) {
      var $this = $(this);
      var radio_name = $this.attr('id');
      if (radio_name.search('team') > 0) {
        toggleTeam(radio_name);
      } else if (radio_name.search('level') > 0) {
        toggleLevel(radio_name);
      }
    });


    //
    // modal actionable
    //
    $body.on('click', '.js-confirm-save', function(event) {
      var $status = $('.admin-section--status .highlighted');
      $status.text('Saved');

      setTimeout(function(){
        $status.fadeOut(function(){
          $status.text('').removeAttr('style');
        });
      }, 5000);
    });


    //
    // select a logo
    //
    $body.on('click', '.js-choose-logo', function(event) {
      event.preventDefault();

      var $self      = $(this),
          $container = $self.closest('.fb-column-container');;

      FB_CTF.modal.loadPopup('choose-logo', function(){
        var $modal = $('#fb-modal');

        FB_CTF.loadComponent('.emblem-carousel', 'inc/components/emblem-carousel.html', function(){
          FB_CTF.slider.init();
        });

        $('.js-store-logo', $modal).on('click', function(event) {
          event.preventDefault();
          var $active  = $('.slides li.active', $modal),
              logo     = $active.html(),
              logoName = $('use', $active).attr('xlink:href').replace('#icon--badge-', '');

          $('.post-avatar', $container).addClass('has-avatar').html(logo);
          $('.logo-name', $container).text(logoName);
        });
      });
    });


    //
    // change the players per team
    //
    $('#fb-admin--players-per-team').on('change', function(event) {
      event.preventDefault();
      var val = $(this).val();

      PLAYERS_PER_TEAM = val;
    });


    //
    // prompt logout
    //
    $('.js-prompt-logout').on('click', function(event) {
      event.preventDefault();
      FB_CTF.modal.loadPopup('action-logout');
    });

  }

  return {
    init: init
  };
})(); // admin
