/**
 *
 * @package site-specific-code
 * @subpackage
 * @since
 */

jQuery( document ).ready( function ( $ ) {
    var modal = $( "#modal" );
    var modalBlocker = $( "#modal-blocker" );
    var currentTabSelector = $( ".current-tab" );
    var innerModel = $( '.content', modal );
    var progressBarContainer = $( "#progress" );
    var progressBar = $( "#progress-bar" );
    var prev = $( "#prev" );
    var next = $( "#next" );
    var esc = $( "#esc" );
    var getStarted = $( "#get-started" );

    var tabSelector = [];
    tabSelector[1] = $( "#tab-1" );
    tabSelector[2] = $( "#tab-2" );
    tabSelector[3] = $( "#tab-3" );
    tabSelector[4] = $( "#tab-4" );
    tabSelector[5] = $( "#tab-5" );

    getStarted.click( function () {
        next.trigger( "click" );
    } );

    prev.click( function () {
        loadTab( prev.data( 'load-tab' ), currentTabSelector.data( 'current-tab' ) );
    } );

    next.click( function () {
        loadTab( next.data( 'load-tab' ), currentTabSelector.data( 'current-tab' ) );
    } );

    $( "body" ).keyup( function ( e ) {
        switch ( e.which ) {
            case 37 :
                prev.trigger( "click" );
                break;
            case 39 :
                next.trigger( "click" );
                break;
            case 27 :
                esc.trigger( "click" );
                break;
        }
    } );

    function loadTab( tabNumber, currentTab ) {
        if ( tabNumber > tabSelector.length - 1 || tabNumber <= 0 )
            return false;

        if ( tabNumber > currentTab ) { // moving left
            var incomplete = checkInputsComplete( currentTabSelector );

            if ( incomplete.length <= 0 ) {
                clearErrors( currentTabSelector );
            } else {
                displayErrors( incomplete );

                return false;
            }
        }
        if ( currentTab > tabNumber ) {
            prev.data( 'load-tab', parseInt( prev.data( 'load-tab' ) ) - 1 );
            next.data( 'load-tab', parseInt( next.data( 'load-tab' ) ) - 1 );
        } else {
            prev.data( 'load-tab', parseInt( prev.data( 'load-tab' ) ) + 1 );
            next.data( 'load-tab', parseInt( next.data( 'load-tab' ) ) + 1 );
        }

        var result;

        switch ( tabNumber ) {
            case 1 :
                result = loadTabOne();
                break;
            case 2 :
                result = loadTabTwo();
                break;
            case 3 :
                result = loadTabThree();
                break;
            case 4 :
                result = loadTabFour();
                break;
            case 5 :
                result = loadTabFive();
                break;
            default :
                result = false;
        }

        currentTabSelector.removeClass( 'current-tab' );

        tabSelector[tabNumber].addClass( 'current-tab' );

        currentTabSelector.refresh();
        prev.refresh();
        next.refresh();

        return result;
    }

    function loadTabOne() {
        prev.addClass( "hidden" );
        progressBar.css( "width", "0%" );
    }

    function loadTabTwo() {
        prev.removeClass( "hidden" );
        progressBar.css( "width", "25%" );
    }

    function loadTabThree() {
        progressBar.css( "width", "50%" );
    }

    function loadTabFour() {
        next.removeClass( "hidden" );
        progressBar.css( "width", "75%" );
    }

    function loadTabFive() {
        next.addClass( "hidden" );
        progressBar.css( "width", "100%" );
    }

    function checkInputsComplete( parentSelector ) {
        var incompleteInputs = [];
        $( "input[required], select[required]", parentSelector ).each( function () {
            if ( $( this ).val().length <= 0 ) {
                incompleteInputs.push( $( this ) );
            }
        } );

        return incompleteInputs;
    }

    function displayErrors( incompleteInputs ) {
        $.each( incompleteInputs, function () {
            $( this ).css( "border", "red 1px solid" );
        } );
    }

    function clearErrors( parentSelector ) {
        $( "input[required], select[required]", parentSelector ).each( function () {
            $( this ).removeAttr( "style" );
        } );
    }

    esc.click( function () {
        $( "body" ).removeClass( "noscroll" );
        modal.hide();
        modalBlocker.hide();
    } );

    /**
     * @author http://stackoverflow.com/questions/11868899/how-can-i-refresh-a-stored-and-snapshotted-jquery-selector-variable
     * @returns {$.fn}
     */
    $.fn.refresh = function () {
        var elems = $( this.selector );
        this.splice( 0, this.length );
        this.push.apply( this, elems );
        return this;
    };
} );