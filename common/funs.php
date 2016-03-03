<?hh // strict

function must_have_idx<Tk, Tv>(
  ?KeyedContainer<Tk, Tv> $arr,
  Tk $idx,
): Tv {
  invariant($arr !== null, 'Container is null');
  $result = idx($arr, $idx);
  invariant($result !== null, sprintf('Index %s not found in container', $idx));
  return $result;
}