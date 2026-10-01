(function (wp) {
	'use strict';
	const el = wp.element.createElement;
	const { useMemo, useState, Fragment, RawHTML } = wp.element;
	const { RichText, InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } = wp.blockEditor;
	const { PanelBody, TextControl, Button, Notice } = wp.components;
	function parse(html) { const container = document.createElement('div'); container.innerHTML = html; return container; }
	function nodeProps(node) {
		const props = {};
		for (const attr of node.attributes) {
			if (attr.name === 'style') {
				props.style = {};
				for (const key of node.style) {
					const reactKey = key.startsWith('--') ? key : key.replace(/^-webkit-/, 'Webkit-').replace(/-([a-z])/g, (_, c) => c.toUpperCase());
					props.style[reactKey] = node.style.getPropertyValue(key);
				}
			} else if (attr.name === 'class') props.className = attr.value;
			else if (attr.name === 'tabindex') props.tabIndex = Number(attr.value);
			else if (attr.name === 'hidden') props.hidden = true;
			else if (!attr.name.startsWith('on')) props[attr.name] = attr.value;
		}
		return props;
	}
	wp.blocks.registerBlockType('ymcatmtb/section', {
		apiVersion: 3, title: 'YMC editable section', icon: 'layout', category: 'design',
		description: 'An independent, visually editable copy of a YMC design section.',
		attributes: { title: { type: 'string', default: 'YMC section' }, html: { type: 'string', source: 'html', selector: '.ymc-section-content', default: '' } },
		supports: { html: false, reusable: false },
		__experimentalLabel: attributes => attributes.title,
		edit: function ({ attributes, setAttributes }) {
			const [selected, select] = useState(null);
			const tree = useMemo(() => parse(attributes.html), [attributes.html]);
			const blockProps = useBlockProps({ className: 'ymc-reference ymc-editable-section' });
			function update(id, callback) {
				const copy = parse(attributes.html);
				const node = copy.querySelector('[data-ymc-node="' + id + '"]');
				if (node) { callback(node); setAttributes({ html: copy.innerHTML }); }
			}
			const selectedNode = selected === null ? null : tree.querySelector('[data-ymc-node="' + selected + '"]');
			function render(node, index) {
				if (node.nodeType === 3) {
					const parentId = node.parentElement?.getAttribute('data-ymc-node');
					if (node.textContent.trim() && parentId !== null && node.parentElement?.tagName === 'A') return el(RichText, { key: 'text-' + index, tagName: 'span', value: node.textContent.trim(), allowedFormats: [], onChange: value => update(parentId, n => { n.childNodes[index].textContent = parse(value).textContent; }) });
					return node.textContent;
				}
				if (node.nodeType !== 1) return null;
				const tag = node.tagName.toLowerCase(), id = node.getAttribute('data-ymc-node');
				const props = { ...nodeProps(node), key: id || index };
				if (tag === 'svg') return el(RawHTML, { key: props.key }, node.outerHTML);
				if (tag === 'img') return el('img', { ...props, onClick: event => { event.preventDefault(); event.stopPropagation(); select(id); }, tabIndex: 0, onFocus: () => select(id) });
				if (tag === 'a') props.onClick = event => { event.preventDefault(); select(id); };
				if (tag === 'button') props.onClick = event => event.preventDefault();
				const textual = ['h1','h2','h3','h4','p','a','span','div'].includes(tag) && node.textContent.trim() && !node.querySelector('div,p,h1,h2,h3,h4,button,img,svg,i') && (!node.children.length || ['h1','h2','h3','h4','p'].includes(tag)) && !node.closest('[data-ymc-countdown]');
				if (textual) return el(RichText, { ...props, tagName: tag, value: node.innerHTML.trim(), allowedFormats: ['core/bold','core/italic', ...(tag === 'a' ? [] : ['core/link'])], onFocus: () => select(id), onChange: value => update(id, n => { n.innerHTML = value; }) });
				return el(tag, props, ...[...node.childNodes].map(render));
			}
			const links = [...tree.querySelectorAll('a')];
			const images = [...tree.querySelectorAll('img')].filter((n,i,a) => a.findIndex(other => other.src === n.src) === i);
			return el(Fragment, null,
				el(InspectorControls, null,
					el(PanelBody, { title: attributes.title, initialOpen: true }, el('p', null, 'Click text in the design to edit it. Changes apply only to this page.'), el(TextControl, { label: 'Section name', value: attributes.title, onChange: title => setAttributes({ title }) })),
					el(PanelBody, { title: 'Links and buttons', initialOpen: selectedNode?.tagName === 'A' }, ...links.map(a => el(TextControl, { key: a.dataset.ymcNode, label: a.textContent.trim() || a.getAttribute('aria-label') || 'Image link', value: a.getAttribute('href') || '', onChange: value => update(a.dataset.ymcNode, n => n.setAttribute('href', value)) }))),
					el(PanelBody, { title: 'Images', initialOpen: selectedNode?.tagName === 'IMG' }, ...images.map(img => el('div', { key: img.dataset.ymcNode, style: { marginBottom: '20px' } },
						el('img', { src: img.src, alt: '', style: { maxWidth: '100%', maxHeight: '90px', background: '#241046' } }),
						el(MediaUploadCheck, null, el(MediaUpload, { allowedTypes: ['image'], onSelect: media => update(img.dataset.ymcNode, n => { n.setAttribute('src', media.url); n.setAttribute('alt', media.alt || ''); }), render: ({ open }) => el(Button, { variant: 'secondary', onClick: open }, 'Replace image') })),
						el(TextControl, { label: 'Image description', value: img.getAttribute('alt') || '', onChange: value => update(img.dataset.ymcNode, n => n.setAttribute('alt', value)) })
					)))
				),
				el('div', blockProps, ...[...tree.childNodes].map(render))
			);
		},
		save: ({ attributes }) => el(RawHTML, null, '<div class="wp-block-ymcatmtb-section ymc-reference"><div class="ymc-section-content">' + attributes.html + '</div></div>')
	});
})(window.wp);
