/**
 * 아로스 버튼 블록 v2
 * ─ 메타박스(_aros_btn_text / _aros_btn_url)에서 값을 읽어 버튼 렌더
 * ─ save() = null → PHP render_callback 사용
 */
(function (blocks, blockEditor, element, components, wpData) {
    var el               = element.createElement;
    var useSelect        = wpData.useSelect;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody        = components.PanelBody;
    var ToggleControl    = components.ToggleControl;
    var Notice           = components.Notice;

    /* ── 에디터 미리보기 스타일 계산 ── */
    function previewStyle(mostLikely) {
        return {
            display:         'block',
            width:           '80%',
            height:          mostLikely ? '96px' : '80px',
            margin:          '0 auto',
            padding:         mostLikely ? '20px 30px' : '10px 20px',
            backgroundColor: 'rgb(240,36,0)',
            color:           '#fff',
            fontFamily:      "'NanumGothicCoding','Malgun Gothic',sans-serif",
            fontSize:        mostLikely ? '42px' : '32px',
            fontWeight:      '700',
            lineHeight:      mostLikely ? '56px' : '60px',
            textAlign:       'center',
            whiteSpace:      'nowrap',
            overflow:        'hidden',
            borderRadius:    '30px',
            boxShadow:       '2px 4px 6px rgba(0,0,0,.4)',
            cursor:          'default',
            textDecoration:  'none',
            boxSizing:       'border-box',
        };
    }

    blocks.registerBlockType('aros/button', {
        title:       '아로스 버튼',
        description: '메타박스에서 지정한 텍스트/URL로 CTA 버튼 출력',
        icon: {
            background: '#f02400',
            foreground: '#fff',
            src: 'button'
        },
        category:    'common',
        keywords:    ['아로스','aros','버튼','cta','신청'],

        attributes: {
            mostLikely: { type: 'boolean', default: false },
        },

        edit: function (props) {
            var mostLikely = props.attributes.mostLikely;
            var setAttr    = props.setAttributes;

            /* 메타박스에서 저장된 현재 포스트의 메타값 */
            var meta = useSelect(function (select) {
                var editor = select('core/editor');
                if (!editor) return {};
                return editor.getEditedPostAttribute('meta') || {};
            }, []);

            var btnText = meta['_aros_btn_text'] || '지금 바로 신청하기 →';
            var btnUrl  = meta['_aros_btn_url']  || '';

            return [
                /* 오른쪽 패널 */
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: '🎯 아로스 버튼', initialOpen: true },
                        el(Notice, { status: 'info', isDismissible: false },
                            '버튼 텍스트/URL 은 포스트 작성화면 하단 "아로스 버튼 설정" 메타박스에서 입력하세요.'
                        ),
                        el(ToggleControl, {
                            label:    '최강 강조 버튼 (더 크게)',
                            help:     '가장 중요한 CTA 1개에만 사용',
                            checked:  mostLikely,
                            onChange: function (v) { setAttr({ mostLikely: v }); }
                        })
                    )
                ),

                /* 편집기 미리보기 */
                el('div', {
                    key: 'preview',
                    style: {
                        padding:    '14px 10px',
                        background: 'repeating-linear-gradient(45deg,#fafbff 0,#fafbff 10px,#f0f3ff 10px,#f0f3ff 20px)',
                        border:     '2px dashed #e95d00',
                        borderRadius: '6px',
                        textAlign:  'center',
                    }
                },
                    el('p', {
                        style: { fontSize: 10, color: '#888', margin: '0 0 10px',
                                 fontWeight: 700, textTransform: 'uppercase', letterSpacing: '.08em' }
                    }, '🎯 아로스 버튼 블록'),
                    el('span', { style: previewStyle(mostLikely) }, btnText),
                    el('p', {
                        style: { fontSize: 11, color: '#aaa', marginTop: 8 }
                    }, btnUrl ? '🔗 ' + btnUrl : '⚠ 하단 메타박스에서 URL 입력 필요')
                )
            ];
        },

        /* PHP render_callback 사용 → null */
        save: function () { return null; }
    });

})(
    window.wp.blocks,
    window.wp.blockEditor,
    window.wp.element,
    window.wp.components,
    window.wp.data
);
