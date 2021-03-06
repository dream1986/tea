<?php
/**
 * This file is part of the Tea programming language project
 *
 * @author 		Benny <benny@meetdreams.com>
 * @copyright 	(c)2019 YJ Technology Ltd. [http://tealang.org]
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Tea;

use Exception;

const _PREFIX_OP_PRECEDENCES = [
	_NEGATION => 2, _BITWISE_NOT => 2, _REFERENCE => 2,
	_NOT => 8,
];

const _BINARY_OP_PRECEDENCES = [
	// L1
	_DOT => 1,  // class/object
	_NOTIFY => 1, // the callback notify
	// () []

	// L2
	// _NEGATION => 2, _BITWISE_NOT => 2, // prefix unary
	_AS => 2, // type cast

	// L3
	_EXPONENTIATION => 3, // math

	// L4
	_MULTIPLICATION => 4, _DIVISION => 4, _REMAINDER => 4, // math
	_SHIFT_LEFT => 4, _SHIFT_RIGHT => 4, _BITWISE_AND => 4, // bitwise

	// L5
	_ADDITION => 5, _SUBTRACTION => 5, // math
	_BITWISE_OR => 5, _BITWISE_XOR => 5, // bitwise

	// L6
	_CONCAT => 6, // String / Array,  concat to the end of the left expression
	_MERGE => 6, // Dict / Array,  merge by key or index
	// 'pop', 'take'  // todo?

	// L7 comparisons
	'<' => 7, '>' => 7, '<=' => 7, '>=' => 7,
	'==' => 7, '===' => 7, _NOT_EQUAL => 7, '!==' => 7, '<=>' => 7,
	_IS => 7, // type / class, and maybe pattern?

	// L8
	// _NOT => 8,

	// L9
	_AND => 9,

	// L10
	_OR => 10, // _XOR => 10,

	// L11
	_NONE_COALESCING => 11,

	// L12
	_CONDITIONAL => 12, // ternary conditional expression
];

class OperatorFactory
{
	static $_dot;
	// static $_notify; // has parsed in the parser

	static $_negation;
	static $_bitwise_not; // eg. ~0 == -1
	static $_reference;

	static $_as;

	static $_exponentiation;

	static $_multiplication;
	static $_division;
	static $_remainder;

	static $_addition;
	static $_subtraction;

	static $_concat;
	static $_merge;

	static $_shift_left;
	static $_shift_right;

	static $_is;
	static $_lessthan;
	static $_morethan;
	static $_lessthan_or_equal;
	static $_morethan_or_equal;
	static $_equal;
	static $_strict_equal;

	static $_not_equal;
	static $_strict_not_equal;

	static $_comparison;

	static $_bitwise_and;
	static $_bitwise_xor;
	static $_bitwise_or;

	static $_none_coalescing;
	static $_conditional; // exp0 ? exp1 : exp2, or exp0 ?: exp1

	static $_bool_not;
	static $_bool_and;
	// static $_bool_xor;
	static $_bool_or;

	private static $prefix_op_symbol_map = [];
	private static $binary_op_symbol_map = [];

	private static $number_op_symbols;
	private static $bitwise_op_symbols;
	private static $bool_op_symbols;

	public static function init()
	{
		self::$_negation = self::create_prefix_operator_symbol(_NEGATION);
		self::$_bitwise_not = self::create_prefix_operator_symbol(_BITWISE_NOT);
		self::$_reference = self::create_prefix_operator_symbol(_REFERENCE);
		self::$_bool_not = self::create_prefix_operator_symbol(_NOT);

		self::$_dot = self::create_normal_operator_symbol(_DOT);
		// self::$_notify = self::create_normal_operator_symbol(_NOTIFY);

		self::$_as = self::create_normal_operator_symbol(_AS);

		self::$_exponentiation = self::create_normal_operator_symbol(_EXPONENTIATION);

		self::$_multiplication = self::create_normal_operator_symbol(_MULTIPLICATION);
		self::$_division = self::create_normal_operator_symbol(_DIVISION);
		self::$_remainder = self::create_normal_operator_symbol(_REMAINDER);

		self::$_addition = self::create_normal_operator_symbol(_ADDITION);
		self::$_subtraction = self::create_normal_operator_symbol(_SUBTRACTION);

		self::$_concat = self::create_normal_operator_symbol(_CONCAT);
		self::$_merge = self::create_normal_operator_symbol(_MERGE);

		self::$_shift_left = self::create_normal_operator_symbol(_SHIFT_LEFT);
		self::$_shift_right = self::create_normal_operator_symbol(_SHIFT_RIGHT);

		self::$_is = self::create_normal_operator_symbol(_IS);

		self::$_lessthan = self::create_normal_operator_symbol('<');
		self::$_morethan = self::create_normal_operator_symbol('>');
		self::$_lessthan_or_equal = self::create_normal_operator_symbol('<=');
		self::$_morethan_or_equal = self::create_normal_operator_symbol('>=');
		self::$_comparison = self::create_normal_operator_symbol('<=>');

		self::$_equal = self::create_normal_operator_symbol('==');
		self::$_strict_equal = self::create_normal_operator_symbol('===');

		self::$_not_equal = self::create_normal_operator_symbol(_NOT_EQUAL);
		self::$_strict_not_equal = self::create_normal_operator_symbol('!==');

		self::$_bitwise_and = self::create_normal_operator_symbol(_BITWISE_AND);
		self::$_bitwise_xor = self::create_normal_operator_symbol(_BITWISE_XOR);
		self::$_bitwise_or = self::create_normal_operator_symbol(_BITWISE_OR);

		self::$_none_coalescing = self::create_normal_operator_symbol(_NONE_COALESCING);

		self::$_conditional = self::create_normal_operator_symbol(_CONDITIONAL);

		self::$_bool_and = self::create_normal_operator_symbol(_AND);
		self::$_bool_or = self::create_normal_operator_symbol(_OR);

		// number
		self::$number_op_symbols = [
			self::$_addition, self::$_subtraction, self::$_multiplication, self::$_division, self::$_remainder, self::$_exponentiation,
			self::$_comparison
		];

		// bitwise
		self::$bitwise_op_symbols = [self::$_bitwise_and, self::$_bitwise_xor, self::$_bitwise_or, self::$_shift_left, self::$_shift_right];

		// bool
		self::$bool_op_symbols = [
			self::$_bool_and, self::$_bool_or,
			self::$_equal, self::$_strict_equal, self::$_not_equal, self::$_strict_not_equal, self::$_is,
			self::$_lessthan, self::$_morethan, self::$_lessthan_or_equal, self::$_morethan_or_equal
		];
	}

	/**
	 * 设置待渲染的目标语言符号映射和优先级
	 * @map array [src sign => dist sign]
	 * @precedences array [dist sign => precedence]
	 */
	public static function set_render_options(array $map, array $precedences)
	{
		foreach (self::$binary_op_symbol_map as $sign => $symbol) {
			$dist_sign = $map[$sign] ?? $sign;

			if (!isset($precedences[$dist_sign])) {
				throw new Exception("Dist precedence of '$dist_sign' not found.");
			}

			$symbol->dist_sign = $dist_sign;
			$symbol->dist_precedence = $precedences[$dist_sign];
		}
	}

	public static function is_number_operator(OperatorSymbol $symbol)
	{
		return in_array($symbol, self::$number_op_symbols, true);
	}

	public static function is_bitwise_operator(OperatorSymbol $symbol)
	{
		return in_array($symbol, self::$bitwise_op_symbols, true);
	}

	public static function is_bool_operator(OperatorSymbol $symbol)
	{
		return in_array($symbol, self::$bool_op_symbols, true);
	}

	public static function get_prefix_operator_symbol(?string $sign)
	{
		return self::$prefix_op_symbol_map[$sign] ?? null;
	}

	public static function get_normal_operator_symbol(?string $sign)
	{
		return self::$binary_op_symbol_map[$sign] ?? null;
	}

	private static function create_prefix_operator_symbol(string $sign)
	{
		return self::$prefix_op_symbol_map[$sign] = new OperatorSymbol($sign, _PREFIX_OP_PRECEDENCES[$sign]);
	}

	private static function create_normal_operator_symbol(string $sign)
	{
		return self::$binary_op_symbol_map[$sign] = new OperatorSymbol($sign, _BINARY_OP_PRECEDENCES[$sign]);
	}
}

