/* -------------------------------------------------------------------------- */
/*                                  Anchor JS                                 */
/* -------------------------------------------------------------------------- */
const anchorInit = () => {
  const anchors = new window.AnchorJS();
  anchors.options = {
    icon: '#',
  };
  anchors.add('[data-anchor]');  
}

export default anchorInit;
