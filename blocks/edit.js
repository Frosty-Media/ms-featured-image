/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n'

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor'
/**
 * WordPress dependencies
 */
import {
  PanelBody,
  TextControl
} from '@wordpress/components'
import { useState, useEffect } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss'

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit ({ attributes, setAttributes }) {
  const {
    excludes
  } = attributes

  const [blogs, setBlogs] = useState([])
  const [sites, setSites] = useState([])

  const blockProps = useBlockProps({
    className: 'pmc-product-card',
    id: 'sites'
  })

  useEffect(() => {
    if (excludes && excludes.trim() !== '') {
      apiFetch({ path: `/ms-featured-image/v1/sites/?exclude=${excludes}`, })
        .then((data) => {
          setSites(data)
        })
    } else {
      apiFetch({ path: `/ms-featured-image/v1/sites/`, })
        .then((data) => {
          setSites(data)
        })
    }
  }, [])

  const getBlogId = () => {
    if (sites && Object.keys(sites).length !== 0) {
      Object.keys(sites).map((site) => {
        useEffect(() => {
          apiFetch({ path: `/ms-featured-image/v1/blog/?blog_id=${site.blog_id}`, })
            .then((data) => {
              setBlogs(data)
            })
        }, [])
      })
    }
  }

  const render = () => {
    // const image = Common::getSiteFeaturedImage( site.blog_id, 'full', false )
    const url = (site) => new URL(site.path, site.domain)
    if (blogs && Object.keys(blogs).length !== 0) {
      Object.keys(blogs).map((site) => {
        return (
          <div>
            <figure>
              <figcaption>
                <a href="{url(site)}" class="animate">{blog.option_value}</a>
              </figcaption>
              <a href="{url}"><img src="{image}" alt="{blog.option_value}"/></a>
            </figure>
          </div>
        )
      }
    }

  }

  return (
    <>

      <InspectorControls>
        <PanelBody title={__('Multisite Featured Image Settings', 'ms-featured-image')} initialOpen={true}>
          <TextControl
            label={__('Blog ID(s)', 'ms-featured-image')}
            value={excludes}
            onChange={(value) => setAttributes({ excludes: value })}
            help={__('Separate with commas or the Enter key.', 'ms-featured-image')}
          />
        </PanelBody>
      </InspectorControls>

      <section {...blockProps}>
        <header>
          <h2>{__('Sites in the network', 'ms-featured-image')}</h2>
        </header>

        <article>
          <div className="row">
            <div>
              {sites}
            </div>
          </div>
        </article>
      </section>

    </>
  )
}
