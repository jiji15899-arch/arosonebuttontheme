<?php
/**
 * adsensefarm_child (GeneratePress 차일드)
 * ─ 아로스 버튼 메타박스 + Gutenberg 블록 + 숏코드 통합
 */

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}


/* ══════════════════════════════════════════════════
   1. 기존 기능 (댓글 플레이스홀더, 글쓴이 박스 등)
   ══════════════════════════════════════════════════ */

add_action( 'generate_after_content', 'add_simple_author_box_to_single_posts' );
function add_simple_author_box_to_single_posts() {
    if ( is_singular( 'post' ) && function_exists( 'wpsabox_author_box' ) ) {
        echo wpsabox_author_box();
    }
}

add_filter( 'comment_form_default_fields', 'tu_filter_comment_fields', 20 );
function tu_filter_comment_fields( $fields ) {
    $commenter = wp_get_current_commenter();
    $fields['author'] = '<label for="author" class="screen-reader-text">' . esc_html__( 'Name', 'generatepress' ) . '</label><input placeholder="닉네임 *" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" />';
    $fields['email']  = '<label for="email" class="screen-reader-text">' . esc_html__( 'Email', 'generatepress' ) . '</label><input placeholder="이메일 *" id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" />';
    $fields['url']    = '<label for="url" class="screen-reader-text">' . esc_html__( 'Website', 'generatepress' ) . '</label><input placeholder="웹사이트" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />';
    return $fields;
}

add_filter( 'generate_post_comment', 'mj_generate_post_comment' );
function mj_generate_post_comment() { return '댓글 등록 &#10230;'; }

add_filter( 'generate_leave_comment', 'tu_custom_leave_comment' );
function tu_custom_leave_comment() { return '댓글 남기기'; }

add_filter( 'generate_more_jump', '__return_false' );


/* ══════════════════════════════════════════════════
   2. 포스트 메타 등록 (REST API 노출 → 블록 에디터 접근 가능)
   ══════════════════════════════════════════════════ */

add_action( 'init', 'aros_register_post_meta' );
function aros_register_post_meta() {
    $args = array(
        'show_in_rest'  => true,   // ← Gutenberg 블록이 useSelect 로 읽으려면 필수
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
        'sanitize_callback' => 'sanitize_text_field',
    );

    register_post_meta( 'post', '_aros_btn_text',      $args );
    register_post_meta( 'post', '_aros_btn_url',
        array_merge( $args, array( 'sanitize_callback' => 'esc_url_raw' ) )
    );
    register_post_meta( 'post', '_aros_btn_most_likely',
        array_merge( $args, array(
            'type'              => 'boolean',
            'default'           => false,
            'sanitize_callback' => function ( $v ) { return (bool) $v; },
        ) )
    );
    register_post_meta( 'post', '_aros_btn_nofollow',
        array_merge( $args, array(
            'type'              => 'boolean',
            'default'           => false,
            'sanitize_callback' => function ( $v ) { return (bool) $v; },
        ) )
    );
}


/* ══════════════════════════════════════════════════
   3. 아로스 버튼 메타박스 (클래식 + 구텐베르크 모두 표시)
   ══════════════════════════════════════════════════ */

add_action( 'add_meta_boxes', 'aros_add_button_metabox' );
function aros_add_button_metabox() {
    add_meta_box(
        'aros_button_metabox',
        '🎯 아로스 버튼 설정',
        'aros_render_button_metabox',
        'post',
        'normal',
        'high'
    );
}

function aros_render_button_metabox( $post ) {
    wp_nonce_field( 'aros_save_button_meta', 'aros_btn_nonce' );

    $text      = get_post_meta( $post->ID, '_aros_btn_text', true );
    $url       = get_post_meta( $post->ID, '_aros_btn_url', true );
    $most      = get_post_meta( $post->ID, '_aros_btn_most_likely', true );
    $nofollow  = get_post_meta( $post->ID, '_aros_btn_nofollow', true );

    ?>
    <style>
        #aros_button_metabox .aros-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 24px;
            padding: 14px 4px 4px;
        }
        #aros_button_metabox .aros-meta-grid .full { grid-column: 1 / -1; }
        #aros_button_metabox label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
        #aros_button_metabox input[type=text],
        #aros_button_metabox input[type=url] {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        #aros_button_metabox .aros-toggle-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        #aros_button_metabox .aros-preview {
            margin-top: 16px;
            padding: 16px;
            background: repeating-linear-gradient(45deg,#fafbff 0,#fafbff 10px,#f0f3ff 10px,#f0f3ff 20px);
            border: 2px dashed #e95d00;
            border-radius: 6px;
            text-align: center;
        }
        #aros_button_metabox .aros-preview-btn {
            display: inline-block;
            background: rgb(240,36,0);
            color: #fff;
            width: 80%;
            height: 56px;
            line-height: 56px;
            border-radius: 20px;
            font-size: 20px;
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
            font-weight: 700;
        }
        #aros_button_metabox .aros-note {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
        }
    </style>

    <div class="aros-meta-grid">
        <div class="full">
            <label for="aros_btn_text">버튼 텍스트</label>
            <input type="text" id="aros_btn_text" name="aros_btn_text"
                   value="<?php echo esc_attr( $text ?: '지금 바로 신청하기 →' ); ?>"
                   placeholder="지금 바로 신청하기 →">
        </div>
        <div class="full">
            <label for="aros_btn_url">버튼 링크 URL</label>
            <input type="url" id="aros_btn_url" name="aros_btn_url"
                   value="<?php echo esc_attr( $url ); ?>"
                   placeholder="https://example.com/apply">
        </div>
        <div>
            <div class="aros-toggle-row">
                <input type="checkbox" id="aros_btn_most_likely" name="aros_btn_most_likely"
                       value="1" <?php checked( $most, '1' ); ?>>
                <label for="aros_btn_most_likely" style="margin:0;font-weight:400;">
                    최강 강조 버튼 (더 크게)
                </label>
            </div>
        </div>
        <div>
            <div class="aros-toggle-row">
                <input type="checkbox" id="aros_btn_nofollow" name="aros_btn_nofollow"
                       value="1" <?php checked( $nofollow, '1' ); ?>>
                <label for="aros_btn_nofollow" style="margin:0;font-weight:400;">
                    rel="nofollow" (광고·제휴 링크)
                </label>
            </div>
        </div>
    </div>

    <div class="aros-preview">
        <span class="aros-preview-btn" id="aros-preview-btn-text">
            <?php echo esc_html( $text ?: '지금 바로 신청하기 →' ); ?>
        </span>
    </div>
    <p class="aros-note">
        * 포스트 본문에 <strong>[aros_button]</strong> 단축코드를 넣거나,
        구텐베르크 블록 "아로스 버튼" 블록을 삽입하면 이 설정값으로 버튼이 표시됩니다.
    </p>

    <script>
    (function(){
        var txtInput = document.getElementById('aros_btn_text');
        var preview  = document.getElementById('aros-preview-btn-text');
        if (!txtInput || !preview) return;
        txtInput.addEventListener('input', function(){
            preview.textContent = this.value || '지금 바로 신청하기 →';
        });
    })();
    </script>
    <?php
}

add_action( 'save_post_post', 'aros_save_button_meta', 10, 2 );
function aros_save_button_meta( $post_id, $post ) {
    /* 검증 */
    if (
        ! isset( $_POST['aros_btn_nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aros_btn_nonce'] ) ), 'aros_save_button_meta' )
    ) return;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    /* 저장 */
    if ( isset( $_POST['aros_btn_text'] ) ) {
        update_post_meta( $post_id, '_aros_btn_text',
            sanitize_text_field( wp_unslash( $_POST['aros_btn_text'] ) ) );
    }
    if ( isset( $_POST['aros_btn_url'] ) ) {
        update_post_meta( $post_id, '_aros_btn_url',
            esc_url_raw( wp_unslash( $_POST['aros_btn_url'] ) ) );
    }

    update_post_meta( $post_id, '_aros_btn_most_likely',
        isset( $_POST['aros_btn_most_likely'] ) ? '1' : '0' );
    update_post_meta( $post_id, '_aros_btn_nofollow',
        isset( $_POST['aros_btn_nofollow'] ) ? '1' : '0' );
}


/* ══════════════════════════════════════════════════
   4. 아로스 버튼 Gutenberg 블록 등록
      save() = null → PHP render_callback 담당
   ══════════════════════════════════════════════════ */

add_action( 'init', 'aros_register_button_block' );
function aros_register_button_block() {
    if ( ! function_exists( 'register_block_type' ) ) return;

    wp_register_script(
        'aros-button-block-js',
        get_stylesheet_directory_uri() . '/blocks/aros-button/block.js',
        array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-components', 'wp-data' ),
        '2.0.0',
        true
    );

    register_block_type( 'aros/button', array(
        'editor_script'   => 'aros-button-block-js',
        'attributes'      => array(
            'mostLikely' => array( 'type' => 'boolean', 'default' => false ),
        ),
        'render_callback' => 'aros_render_button_block',
    ) );
}

/**
 * PHP 렌더 콜백: 포스트 메타에서 읽어서 HTML 반환
 */
function aros_render_button_block( $attrs, $content, $block ) {
    $post_id   = isset( $block->context['postId'] )
                    ? (int) $block->context['postId']
                    : get_the_ID();

    $text      = get_post_meta( $post_id, '_aros_btn_text', true );
    $url       = get_post_meta( $post_id, '_aros_btn_url',  true );
    $most      = get_post_meta( $post_id, '_aros_btn_most_likely', true );
    $nofollow  = get_post_meta( $post_id, '_aros_btn_nofollow', true );

    /* 텍스트·URL 없으면 기본값 */
    if ( empty( $text ) ) $text = '지금 바로 신청하기 →';
    if ( empty( $url )  ) $url  = '#';

    $class     = 'aros-button' . ( $most ? ' most-likely-to-click' : '' );
    $rel_parts = array( 'noopener' );
    if ( $nofollow ) $rel_parts[] = 'nofollow';
    $rel = implode( ' ', $rel_parts );

    return sprintf(
        '<p style="text-align:center"><a href="%s" class="%s" target="_blank" rel="%s">%s</a></p>',
        esc_url( $url ),
        esc_attr( $class ),
        esc_attr( $rel ),
        esc_html( $text )
    );
}


/* ══════════════════════════════════════════════════
   5. 숏코드 [aros_button] — 클래식 에디터 / 본문 어디서나
   ══════════════════════════════════════════════════ */

add_shortcode( 'aros_button', 'aros_shortcode_button' );
function aros_shortcode_button( $atts ) {
    $post_id  = get_the_ID();
    $text     = get_post_meta( $post_id, '_aros_btn_text', true ) ?: '지금 바로 신청하기 →';
    $url      = get_post_meta( $post_id, '_aros_btn_url',  true ) ?: '#';
    $most     = get_post_meta( $post_id, '_aros_btn_most_likely', true );
    $nofollow = get_post_meta( $post_id, '_aros_btn_nofollow', true );

    /* 숏코드 속성으로 오버라이드 가능 */
    $a = shortcode_atts( array(
        'text'  => $text,
        'url'   => $url,
        'most'  => $most,
    ), $atts );

    $class = 'aros-button' . ( $a['most'] ? ' most-likely-to-click' : '' );
    $rel   = $nofollow ? 'noopener nofollow' : 'noopener';

    return sprintf(
        '<p style="text-align:center"><a href="%s" class="%s" target="_blank" rel="%s">%s</a></p>',
        esc_url( $a['url'] ),
        esc_attr( $class ),
        esc_attr( $rel ),
        esc_html( $a['text'] )
    );
}


/* ══════════════════════════════════════════════════
   6. 프론트엔드 JS (ripple 효과) — 조건부 로드
   ══════════════════════════════════════════════════ */

add_action( 'wp_enqueue_scripts', 'aros_enqueue_frontend_js' );
function aros_enqueue_frontend_js() {
    if ( ! is_singular( 'post' ) ) return;

    global $post;
    if ( ! $post ) return;

    $has_block = has_block( 'aros/button', $post );
    $has_sc    = has_shortcode( $post->post_content, 'aros_button' );
    $has_url   = (bool) get_post_meta( $post->ID, '_aros_btn_url', true );

    if ( $has_block || $has_sc || $has_url ) {
        wp_enqueue_script(
            'aros-button-frontend',
            get_stylesheet_directory_uri() . '/assets/js/aros-button.js',
            array(),
            '2.0.0',
            true
        );
    }
}
