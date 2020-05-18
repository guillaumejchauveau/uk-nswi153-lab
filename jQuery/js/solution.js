let _confirmDialog = null
let _promptDialog = null

/**
 * List-item objects.
 */
class ListItem {

  /**
   * Injects dependencies into list item class.
   * @param {function} confirmDialog
   * @param {function} promptDialog
   */
  static init (confirmDialog, promptDialog) {
    _confirmDialog = confirmDialog
    _promptDialog = promptDialog
  }

  /**
   * Initialize new node with no children
   * @param {string} caption
   * @param {ListItem} parent
   */
  constructor (caption, parent = null) {
    this.caption = caption  // this node's caption
    this.children = []      // array of child nodes (ListItem objects)
    this.parent = parent      // reference to a parent ListItem (null for root)
    this.collapsed = false  // whether the children are visible
    this._domObj = null     // reference to corresponding DOM <li> element
    this._domParent = null  // reference to parent DOM <ul> element
  }

  /**
   * Internal handler of onclick event (expand/collapses the node).
   */
  _clickHandler = ev => {
    ev.stopPropagation()
    if (this.collapsed) {
      this.expand()
    } else {
      this.collapse()
    }
  }

  /**
   * Internal handler of the double click (starts editing the node's caption).
   */
  _dblclickHandler = ev => {
    ev.stopPropagation()
    this._promptForName('Enter new name', this.caption)
      .then(name => this.setCaption(name))
      .catch(() => {})
  }

  /**
   * Internal handler of delete icon onclick (removes the node).
   */
  _deleteIconHandler = ev => {
    ev.stopPropagation()

    _confirmDialog('Remove ?')
      .then(() => this.remove())
      .catch(() => {})
  }

  /**
   * Internal handler of the add-list icon onclick. Creates sub-list and fill its first item from the prompt.
   */
  _addlistIconHandler = ev => {
    ev.stopPropagation()

    this._promptForName('Caption:')
      .then(caption => {
        const newItem = new ListItem(caption, this)
        this.addChild(newItem)
        this.render()
      })
      .catch(() => {})
  }

  /**
   * Internal handler of add icon onclick. Inserts new item at the end of corresponding list.
   */
  _addIconHandler = ev => {
    ev.stopPropagation()

    this._promptForName('Caption:')
      .then(caption => {
        const newItem = new ListItem(caption, this)
        this.addChild(newItem)
        this.render()
      })
      .catch(() => {})
  }

  /**
   * Internal function that prompts for new/change of caption.
   * It also highlights current node whilst inquiry is being posed.
   * @param {string} inquiry Message to be displayed in the prompt
   * @param {string} value Initial value of the input box
   * @return {Promise} Promise representing the prompt operation, resolving in a string
   */
  _promptForName (inquiry, value = '') {
    if (this._domObj) {
      this._domObj.addClass('selected')
    }
    return _promptDialog(inquiry, value).finally(() => {
      if (this._domObj) {
        this._domObj.removeClass('selected')
      }
    })
  }

  /**
   * Internal function that creates image with an icon.
   * @param {string} name Identifier (file name witout extension)
   * @param {string} title Title displayed on mouse hover
   * @param {function} onclickHandler Onclick (action) callback for the icon
   * @return {object} jQuery object representing the image
   */
  _createImg (name, title, onclickHandler) {
    const img = $(`<img src="style/${name}.png" alt="${name}" title="${title}" />`)
    if (onclickHandler) {
      img.click(onclickHandler)
    }
    return img
  }

  /**
   * Internal function that create icons list.
   * @param {boolean} del Delete (remove) icon should be displayed
   * @param {boolean} addList Add sub-list (children) icon should be displayed
   * @param {boolean} add Add item (append) icon should be displayed
   * @return {object} jQuery object holding the container with the icons
   */
  _createIcons (del, addList, add) {
    const icons = $('<span class="icons"></span>')		// icons container

    if (del) {
      icons.append(this._createImg('delete', 'Delete', this._deleteIconHandler))
    }
    if (addList) {
      icons.append(this._createImg('add-list', 'Add Sub-List', this._addlistIconHandler))
    }
    if (add) {
      icons.append(this._createImg('add', 'Add Item', this._addIconHandler))
    }

    return icons
  }

  /**
   * Render the list item into DOM.
   * @param {object} domParent DOM element which acts as container (parent). Should be an <ul> element.
   *               If domParent is null (missing), the DOM parent from the last call is used.
   */
  render (domParent = null) {
    const rerender = !domParent
    domParent = domParent || this._domParent
    if (!domParent) return
    // Root item.
    if (domParent.is('div')) {
      domParent.empty()
      const domChildren = $('<ul></ul>')
      this.children.forEach(child => child.render(domChildren))

      $('<li></li>')
        .append(this._createIcons(false, false, true))
        .appendTo(domChildren)

      domParent.append(domChildren)
      this._domParent = domParent
      return
    }

    let domObj = $('<li></li>')
      .text(this.caption)
      .on('click', this._clickHandler)
      .on('dblclick', this._dblclickHandler)
      .append(this._createIcons(true, this.children.length === 0, false))
    if (this.collapsed) {
      domObj.addClass('collapsed')
    }

    if (this.children.length !== 0) {
      const domChildren = $('<ul></ul>')
      this.children.forEach(child => child.render(domChildren))

      $('<li></li>')
        .append(this._createIcons(false, false, true))
        .appendTo(domChildren)

      domObj
        .append(domChildren)
        .addClass('parent')
    }

    if (rerender) {
      this._domObj.replaceWith(domObj)
    } else {
      domParent.append(domObj)
    }
    // Make sure we remember, which DOM objects we have ...
    this._domObj = domObj
    this._domParent = domParent
  }

  /**
   * @param {ListItem} child The list item object that will be appended to the child list.
   */
  addChild (child) {
    if (!(child instanceof ListItem)) {
      throw new Error('Given child is not a ListItem object.')
    }
    this.children.push(child)
    if (this._domObj) {
      this.render()
    }
  }

  /**
   * Change the caption of the item.
   * @param {string} caption New caption of the list item
   */
  setCaption (caption) {
    this.caption = caption
    this.render()
  }

  /**
   * Make the element collapsed (hide its children).
   */
  collapse () {
    this._domObj.addClass('collapsed')
    this.collapsed = true
  }

  /**
   * Make the element expanded (show its children).
   */
  expand () {
    this._domObj.removeClass('collapsed')
    this.collapsed = false
  }

  /**
   * Remove the element from the tree structure (i.e., from the children list of its parent).
   */
  remove () {
    if (!this.parent) {
      throw new Error('The root element cannot be removed by this function.')
    }

    this.parent.children = this.parent.children.filter(child => child !== this)
    this.parent.render()
  }

  /**
   * Helper function that builds a whole nested list from given data object.
   * @param {string|object} container jQuery selector/object pointing to DOM element which contains the list.
   * @param {Array} data The data structure from which the list is constructed
   * @return {ListItem} Root object of the constructed tree
   */
  static buildNestedList (container, data) {
    // Internal recursive function that builds a ListItem (and all its children) from given data item.
    const build = (dataItem, parent) => {
      const item = new ListItem(dataItem.caption, parent)
      if (dataItem.children) {
        dataItem.children.map(child => build(child, item)).forEach(child => item.addChild(child))
      }
      return item
    }

    // Prepare the container.
    container = $(container)
    container.addClass('nested-list-container')
    container.empty()

    // Build the root from fake dataItem which holds the data as children (root stands above these data).
    const root = build({ caption: '', children: data }, null)
    root.render(container)
    return root
  }
}

module.exports = { ListItem }
