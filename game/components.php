<?hh

abstract class :xhp:svg-element extends :xhp:html-element {
}

class :svg extends :xhp:svg-element {
  attribute
    int width,
    int height,
    Stringish xmlns,
    Stringish xmlns:amcharts,
    Stringish xmlns:xlink,
    Stringish viewBox,
    Stringish data-file,
    Stringish preserveAspectRatio;
  category %flow, %phrase;

  children (:use | :defs | :g)*;
  protected $tagName = 'svg';
}

class :path extends :xhp:svg-element {
  attribute
    Stringish d;

  protected $tagName = 'path';
}

class :use extends :xhp:svg-element {
  attribute
    Stringish xlink:href;

  protected $tagName = 'use';
}

class :defs extends :xhp:svg-element {
  attribute
    Stringish d,
    Stringish transform;

  children (:amcharts:ammap)*;
  protected $tagName = 'defs';
}

class :amcharts:ammap extends :xhp:svg-element {
  attribute
    Stringish projection,
    Stringish leftLongitude,
    Stringish topLatitude,
    Stringish rightLongitude,
    Stringish bottomLatitude;

  protected $tagName = 'amcharts:ammap';
}

class :g extends :xhp:svg-element {
  attribute
    Stringish d,
    Stringish transform,
    Stringish data-captured;

  children (:path | :g)*;
  protected $tagName = 'g';
}